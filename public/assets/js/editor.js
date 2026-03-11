const Editor = (() => {

    /* ===============================
       CONFIG
    =============================== */

    const MM_TO_PX = 3.4;
    const PX_TO_MM = 1 / MM_TO_PX;
    const GRID     = 1;

    let config = {};
    let canvas = null;
    let SCALE  = 3;
    let selectedElement = null;
    

    // Controle global de interações
    let currentResize = null;
    let currentDrag   = null;

    /* ===============================
       STATE
    =============================== */

    let state = {
        elements: [],
        selectedId: null
    };

    function initState(initialData) {
        if (initialData && initialData.elements) {
            state.elements = initialData.elements;
        }
    }

    function addElement(element) {
        state.elements.push(element);
    }

    function updateElement(id, newData) {
        const index = state.elements.findIndex(e => e.id === id);
        if (index === -1) return;

        const current = state.elements[index];

        state.elements[index] = {
            ...current,
            ...newData,
            position: {
                ...current.position,
                ...newData.position
            },
            size: {
                ...current.size,
                ...newData.size
            },
            content: {
                ...current.content,
                ...newData.content
            },
            style: {
                ...current.style,
                ...newData.style
            },
            barcode: {
                ...current.barcode,
                ...newData.barcode
            }
        };
    }

    function deleteSelectedElement() {
        const el = getSelectedElement();
        if (!el) return;

        state.elements = state.elements.filter(e => e.id !== el.id);
        const div = document.querySelector(`[data-id="${el.id}"]`);
        if (div) div.remove();

        state.selectedId = null;
        clearPropertiesPanel();
    }

    function getSelectedElement() {
        return state.elements.find(e => e.id === state.selectedId);
    }

    function selectElement(id) {
        state.selectedId = id;

        document
            .querySelectorAll('.label-element')
            .forEach(el => el.classList.remove('selected'));

        const selected = document.querySelector(`[data-id="${id}"]`);
        if (selected) selected.classList.add('selected');

        loadPropertiesPanel();
    }

    function exportJSON() {
        return JSON.stringify({ elements: state.elements });
    }

    /* ===============================
       GLOBAL INTERACTIONS
    =============================== */

    function bindGlobalInteractions() {

        document.addEventListener('mousemove', function(e) {

            // DRAG
            if (currentDrag) {
                const { div, element, offsetX, offsetY } = currentDrag;

                let x = e.clientX - offsetX;
                let y = e.clientY - offsetY;

                const maxX = canvas.clientWidth - div.offsetWidth;
                const maxY = canvas.clientHeight - div.offsetHeight;

                if (x < 0) x = 0;
                if (y < 0) y = 0;

                if (x > maxX) x = maxX;
                if (y > maxY) y = maxY;

                x = mmToPx(Math.round(pxToMm(x) / GRID) * GRID);
                y = mmToPx(Math.round(pxToMm(y) / GRID) * GRID);

                div.style.left = x + 'px';
                div.style.top  = y + 'px';

                updateElement(element.id, {
                    position: {
                        x_mm: pxToMm(x),
                        y_mm: pxToMm(y)
                    }
                });

                loadPropertiesPanel();
            }

            // RESIZE
            if (currentResize) {
                const { div, startWidth, startHeight, startMouseX, startMouseY, element } = currentResize;

                const dx = e.clientX - startMouseX;
                const dy = e.clientY - startMouseY;

                let newWidth  = startWidth + dx;
                let newHeight = startHeight + dy;

                const maxWidth  = canvas.clientWidth - div.offsetLeft;
                const maxHeight = canvas.clientHeight - div.offsetTop;

                if (newWidth > maxWidth) newWidth = maxWidth;
                if (newHeight > maxHeight) newHeight = maxHeight;

                if (newWidth < 10 || newHeight < 10) return;

                div.style.width  = newWidth + 'px';

                if (element.type !== 'text') {
                    div.style.height = newHeight + 'px';
                }

                updateElement(element.id, {
                    size: {
                        width_mm: pxToMm(newWidth),
                        height_mm: element.type === 'text'
                            ? pxToMm(div.scrollHeight)
                            : pxToMm(newHeight)
                    }
                });

                loadPropertiesPanel();
            }
        });

        document.addEventListener('mouseup', function() {
            currentDrag = null;
            currentResize = null;
        });
    }

    /* ===============================
       UTILS
    =============================== */

    function mmToPx(mm) {
        return mm * MM_TO_PX * SCALE;
    }

    function pxToMm(px) {
        return px * PX_TO_MM / SCALE;
    }

    function generateId() {
        return crypto.randomUUID();
    }

    /* ===============================
       ACTIONS (Canvas)
    =============================== */

    function clearCanvas() {
        if (!canvas) return;

        canvas.innerHTML = '';
        state.elements = [];
        state.selectedId = null;
        selectedElement = null;
        clearPropertiesPanel();
    }

    function serializeCanvas() {
        return {
            config,
            elements: state.elements
        };
    }

    function bindFooterButtons() {
        const btnClear   = document.getElementById('btn-clear-canvas');
        const btnSave    = document.getElementById('btn-save-canvas');
        const layoutUuid = document.getElementById('layout-uuid');

        if (btnClear) {
            btnClear.addEventListener('click', () => {

                const selected = getSelectedElement();
                const hasSelection = !!selected;

                Swal.fire({
                    title: hasSelection ? 'Excluir elemento?' : 'Limpar layout?',
                    text: hasSelection
                        ? 'Deseja remover o elemento selecionado?'
                        : 'Todos os elementos do layout serão removidos.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: hasSelection ? 'Sim, excluir elemento' : 'Sim, limpar tudo',
                    cancelButtonText: 'Cancelar'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    if (hasSelection) {
                        deleteSelectedElement();
                        Swal.fire('Removido!', 'O elemento foi excluído.', 'success');
                    } else {
                        clearCanvas();
                        Swal.fire('Limpo!', 'Todos os elementos foram removidos.', 'success');
                    }
                });

            });
        }

        if (btnSave) {
            btnSave.addEventListener('click', async () => {
                try {

                    const payload = serializeCanvas();
                    console.log(payload);

                    payload.uuid = layoutUuid ? layoutUuid.value : null;
                    if (!payload.uuid) {
                        Swal.fire('Atenção', 'Layout não encontrado/disponível!', 'info');
                        return;
                    }

                    const response = await fetch('/api/updated-layout', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) {
                        const errorData = await response.json().catch(() => ({}));
                        throw new Error(errorData.message || 'Falha ao salvar layout, verifique os dados e tente novamente!');
                    }

                    const data = await response.json();
                    Swal.fire({
                        title: 'Salvo!',
                        text: 'Layout salvo com sucesso!',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });

                } catch (error) {

                    console.error('Erro ao salvar:', error);
                    Swal.fire({
                        title: 'Erro',
                        text: error.message || 'Não foi possível salvar o layout.',
                        icon: 'error'
                    });
                }
            });
        }
    }

    /* ===============================
       CANVAS
    =============================== */

    function calculateLabelSize() {
        return {
            width_mm: config.label_width_mm,
            height_mm: config.label_height_mm
        };
    }

    function initCanvas() {

        canvas = document.getElementById('label-canvas');

        const labelSize = calculateLabelSize();

        canvas.style.width  = mmToPx(labelSize.width_mm) + 'px';
        canvas.style.height = mmToPx(labelSize.height_mm) + 'px';

        canvas.addEventListener('click', function() {
            state.selectedId = null;

            document
                .querySelectorAll('.label-element')
                .forEach(el => el.classList.remove('selected'));

            clearPropertiesPanel();
        });

        state.elements.forEach(renderElement);
    }

    /* ===============================
       ELEMENTS
    =============================== */

    function createTextElement() {

        const element = {
            id: generateId(),
            type: "text",
            position: { x_mm: 5, y_mm: 5 },
            size: { width_mm: 30, height_mm: 8 },
            rotation: 0,
            content: {
                mode: "static",
                value: "Novo Texto"
            },
            style: {
                font_size: 12,
                font_weight: "normal",
                text_align: "left"
            }
        };

        addElement(element);
        renderElement(element);
    }

    function createBarcodeElement() {

        const element = {
            id: generateId(),
            type: "barcode",
            position: { x_mm: 5, y_mm: 5 },
            size: { width_mm: 40, height_mm: 20 },
            rotation: 0,
            content: {
                mode: "dynamic",
                value: "codigo"
            },
            barcode: {
                type: "EAN13",
                show_text: true
            }
        };

        addElement(element);
        renderElement(element);
    }

    function createQrCodeElement() {

        const element = {
            id: generateId(),
            type: "qrcode",
            position: { x_mm: 5, y_mm: 5 },
            size: { width_mm: 30, height_mm: 30 },
            rotation: 0,
            content: {
                mode: "dynamic",
                value: "qr_data"
            },
            qrcode: {
                error_correction: "M"
            }
        };

        addElement(element);
        renderElement(element);
    }

    function createImageElement() {

        const element = {
            id: generateId(),
            type: "image",
            position: { x_mm: 5, y_mm: 5 },
            size: { width_mm: 30, height_mm: 30 },
            rotation: 0,
            content: {
                mode: "dynamic",
                value: "imagem_url"
            }
        };

        addElement(element);
        renderElement(element);
    }

    function renderElement(element) {

        const div = document.createElement('div');
        div.classList.add('label-element');
        div.dataset.id = element.id;

        applyElementStyles(div, element);

        div.addEventListener('click', function(e) {
            e.stopPropagation();
            selectElement(element.id);
        });

        div.addEventListener('mousedown', function(e) {
            if (e.target.classList.contains('resize-handle')) return;

            currentDrag = {
                div,
                element,
                offsetX: e.clientX - div.offsetLeft,
                offsetY: e.clientY - div.offsetTop
            };

            selectElement(element.id);
        });

        const handle = document.createElement('div');
        handle.classList.add('resize-handle');
        div.appendChild(handle);

        handle.addEventListener('mousedown', function(e) {
            e.stopPropagation();

            currentResize = {
                div,
                element,
                startWidth: div.offsetWidth,
                startHeight: div.offsetHeight,
                startMouseX: e.clientX,
                startMouseY: e.clientY
            };

            selectElement(element.id);
        });

        canvas.appendChild(div);
    }

    function applyElementStyles(div, element) {

        // Reset conteúdo e estilos básicos
        div.innerHTML = '';
        div.style.outline = '';
        div.style.background = '';

        /* ======================
        CONTEÚDO
        ====================== */

        if (element.type === 'barcode') {

            const type = element.barcode?.type || 'EAN13';
            const modeLabel = element.content?.mode === 'dynamic'
                ? `{{ ${element.content.value || 'campo'} }}`
                : (element.content?.value || '');

            div.innerHTML = `
                <div style="width:100%; height:100%; position:relative; display:flex; align-items:center; justify-content:center;">
                    <img 
                        src="../assets/img/illustrations/EAN13.png"
                        style="width:100%; height:100%; object-fit:contain; pointer-events:none; opacity:0.85;"
                    />
                    <div style="
                        position:absolute;
                        bottom:2px;
                        right:4px;
                        font-size:9px;
                        color:#198754;
                        font-weight:bold;
                        background:rgba(255,255,255,0.85);
                        padding:0 3px;
                        border-radius:2px;
                    ">
                        ${type}
                    </div>
                    ${modeLabel ? `
                        <div style="
                            position:absolute;
                            top:2px;
                            left:4px;
                            font-size:9px;
                            color:#198754;
                            background:rgba(255,255,255,0.85);
                            padding:0 3px;
                            border-radius:2px;
                        ">
                            ${modeLabel}
                        </div>
                    ` : ''}
                </div>
            `;

            div.style.outline = '1px dashed #198754';
            div.style.background = 'rgba(25,135,84,0.05)';

        } else if (element.type === 'qrcode') {

            const modeLabel = element.content?.mode === 'dynamic'
                ? `{{ ${element.content.value || 'campo'} }}`
                : (element.content?.value || '');

            div.innerHTML = `
                <div style="width:100%; height:100%; position:relative; display:flex; align-items:center; justify-content:center;">
                    <img 
                        src="../assets/img/illustrations/qr-code.png"
                        style="width:100%; height:100%; object-fit:contain; pointer-events:none; opacity:0.9;"
                    />
                    ${modeLabel ? `
                        <div style="
                            position:absolute;
                            bottom:2px;
                            right:4px;
                            font-size:9px;
                            color:#6f42c1;
                            font-weight:bold;
                            background:rgba(255,255,255,0.85);
                            padding:0 3px;
                            border-radius:2px;
                        ">
                            ${modeLabel}
                        </div>
                    ` : ''}
                </div>
            `;

            div.style.outline = '1px dashed #6f42c1';
            div.style.background = 'rgba(111,66,193,0.05)';

        } else if (element.type === 'text') {

            if (element.content?.mode === 'dynamic') {
                div.innerText = `{{ ${element.content.value || 'campo'} }}`;
                div.style.outline = '1px dashed #0d6efd';
                div.style.background = 'rgba(13,110,253,0.05)';
            } else {
                div.innerText = element.content?.value || '';
            }

        } else if (element.type === 'image') {

            const modeLabel = element.content?.mode === 'dynamic'
                ? `{{ ${element.content.value || 'campo'} }}`
                : (element.content?.value || '');

            div.innerHTML = `
                <div style="width:100%; height:100%; position:relative; display:flex; align-items:center; justify-content:center;">
                    <img 
                        src="../assets/img/illustrations/image.png"
                        style="width:100%; height:100%; object-fit:contain; pointer-events:none; opacity:0.85;"
                    />
                    ${modeLabel ? `
                        <div style="
                            position:absolute;
                            bottom:2px;
                            right:4px;
                            font-size:9px;
                            color:#fd7e14;
                            font-weight:bold;
                            background:rgba(255,255,255,0.85);
                            padding:0 3px;
                            border-radius:2px;
                        ">
                            ${modeLabel}
                        </div>
                    ` : ''}
                </div>
            `;

            div.style.outline = '1px dashed #fd7e14';
            div.style.background = 'rgba(253,126,20,0.05)';
        }

        /* ======================
        POSIÇÃO E TAMANHO
        ====================== */

        div.style.left   = mmToPx(element.position.x_mm) + 'px';
        div.style.top    = mmToPx(element.position.y_mm) + 'px';
        div.style.width  = mmToPx(element.size.width_mm) + 'px';

        if (element.type === 'text') {
            div.style.height = 'auto';
        } else {
            div.style.height = mmToPx(element.size.height_mm) + 'px';
        }

        /* ======================
        ESTILO DE TEXTO
        ====================== */

        if (element.style) {
            div.style.fontSize   = (element.style.font_size || 12) + 'px';
            div.style.fontWeight = element.style.font_weight || 'normal';
            div.style.textAlign  = element.style.text_align || 'left';
        }

        /* ======================
        AUTO HEIGHT (TEXT)
        ====================== */

        if (element.type === 'text') {
            const newHeightPx = div.scrollHeight;
            updateElement(element.id, {
                size: {
                    ...element.size,
                    height_mm: pxToMm(newHeightPx)
                }
            });
        }
    }

    function rerenderElement(id) {
        const elData = state.elements.find(e => e.id === id);
        const div = document.querySelector(`[data-id="${id}"]`);
        if (!div || !elData) return;

        applyElementStyles(div, elData);
    }

    /* ===============================
       PROPERTIES PANEL
    =============================== */

    function loadPropertiesPanel() {
        const el = getSelectedElement();
        if (!el) return;

        // Campos básicos
        document.getElementById('prop-x').value = el.position.x_mm;
        document.getElementById('prop-y').value = el.position.y_mm;
        document.getElementById('prop-width').value  = el.size.width_mm;
        document.getElementById('prop-height').value = el.size.height_mm;
        document.getElementById('prop-content').value = el.content?.value ?? '';

        // Modo (static / dynamic)
        const propMode = document.getElementById('prop-mode');
        if (propMode) propMode.value = el.content?.mode || 'static';

        // Estilos de texto (apenas se existir style)
        if (el.style) {
            const fontSizeInput  = document.getElementById('prop-font-size');
            const textAlignInput = document.getElementById('prop-text-align');

            if (fontSizeInput)  fontSizeInput.value  = el.style.font_size ?? 12;
            if (textAlignInput) textAlignInput.value = el.style.text_align ?? 'left';
        }

        /* =========================
        BARCODE
        ========================= */
        const barcodeWrapper = document.getElementById('barcode-type-wrapper');
        const barcodeTypeSelect = document.getElementById('prop-barcode-type');

        if (el.type === 'barcode') {
            barcodeWrapper.style.display = '';
            barcodeTypeSelect.value = el.barcode?.type || 'EAN13';
        } else {
            barcodeWrapper.style.display = 'none';
        }
    }

    function clearPropertiesPanel() {
        [
            'prop-x','prop-y','prop-width','prop-height','prop-content',
            'prop-font-size','prop-text-align', 'prop-barcode-type'
        ].forEach(id => {
            const input = document.getElementById(id);
            if (input) input.value = '';
        });
    }

    function bindPropertyInputs() {

        const propX             = document.getElementById('prop-x');
        const propY             = document.getElementById('prop-y');
        const propWidth         = document.getElementById('prop-width');
        const propHeight        = document.getElementById('prop-height');
        const propContent       = document.getElementById('prop-content');
        const propMode          = document.getElementById('prop-mode');
        const propFontSize      = document.getElementById('prop-font-size');
        const propTextAlign     = document.getElementById('prop-text-align');
        const propBarcodeType   = document.getElementById('prop-barcode-type');
        const propQrError       = document.getElementById('prop-qrcode-error');

        function applyChanges() {
            const el = getSelectedElement();
            if (!el) return;

            updateElement(el.id, {
                position: {
                    x_mm: parseFloat(propX.value) || 0,
                    y_mm: parseFloat(propY.value) || 0
                },
                size: {
                    width_mm: parseFloat(propWidth.value) || 1,
                    height_mm: parseFloat(propHeight.value) || 1
                },
                content: {
                    mode: propMode ? propMode.value : el.content.mode,
                    value: propContent ? propContent.value : el.content.value
                },
                style: {
                    font_size: parseFloat(propFontSize?.value) || el.style?.font_size || 12,
                    text_align: propTextAlign?.value || el.style?.text_align || 'left'
                },
                barcode: {
                    type: propBarcodeType?.value || el.barcode.type,
                },
                qrcode: {
                    error_correction: propQrError?.value || el.qrcode?.error_correction || 'M'
                },
                // image: {
                //     ...current.image,
                //     ...newData.image
                // }
            });

            rerenderElement(el.id);
        }

        [propX, propY, propWidth, propHeight, propContent, propMode, propFontSize, propTextAlign, propBarcodeType, propQrError]
            .forEach(input => {
                if (input) input.addEventListener('input', applyChanges);
            });
    }

    /* ===============================
       INIT
    =============================== */

    function init(editorConfig, initialState) {

        config = editorConfig;

        initState(initialState);
        initCanvas();
        bindGlobalInteractions();
        bindPropertyInputs();
        bindFooterButtons();

        const barcodeWrapper = document.getElementById('barcode-type-wrapper');
        if (barcodeWrapper) barcodeWrapper.style.display = 'none';

        document
            .getElementById('add-text-btn')
            .addEventListener('click', createTextElement);
        document
            .getElementById('add-barcode-btn')
            .addEventListener('click', createBarcodeElement);
        document
            .getElementById('add-qrcode-btn')
            .addEventListener('click', createQrCodeElement);
        document
            .getElementById('add-image-btn')
            .addEventListener('click', createImageElement);
    }

    return {
        init,
        exportJSON,
        serializeCanvas,
        clearCanvas
    };

})();