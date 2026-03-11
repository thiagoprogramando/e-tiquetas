const SCALE = 5;

document.querySelectorAll('.label-preview').forEach(container => {

    let canvas = container.dataset.canvas;

    try {
        canvas = JSON.parse(canvas);
    } catch(e){
        return;
    }

    canvas.forEach(obj => {

        const el = document.createElement('div');

        const x = (obj.position?.x_mm || 0) * SCALE;
        const y = (obj.position?.y_mm || 0) * SCALE;
        const w = (obj.size?.width_mm || 10) * SCALE;
        const h = (obj.size?.height_mm || 5) * SCALE;

        el.style.position = 'absolute';
        el.style.left = x + 'px';
        el.style.top = y + 'px';
        el.style.width = w + 'px';
        el.style.height = h + 'px';

        if(obj.type === 'text'){
            el.innerText = obj.content?.value || 'Texto';
            el.style.fontSize = (obj.style?.font_size || 12) * 0.6 + 'px';
            el.style.textAlign = obj.style?.text_align || 'left';
            el.style.lineHeight = '1.1';
            el.style.fontWeight = obj.style?.font_weight || 'normal';
            el.style.overflow = 'hidden';
        }

        if(obj.type === 'barcode'){
            const img = document.createElement('img');
            img.src = "../assets/img/illustrations/EAN13.png";
            img.style.width = "100%";
            img.style.height = "100%";
            img.style.objectFit = "contain";
            el.appendChild(img);
        }

        if(obj.type === 'qrcode'){
            const img = document.createElement('img');
            img.src = "../assets/img/illustrations/qr-code.png";
            img.style.width = "100%";
            img.style.height = "100%";
            img.style.objectFit = "contain";
            el.appendChild(img);
        }

        if(obj.type === 'image'){
            const img = document.createElement('img');
            img.src = "../assets/img/illustrations/image.png";
            img.style.width = "100%";
            img.style.height = "100%";
            img.style.objectFit = "contain";
            el.appendChild(img);
        }

        container.appendChild(el);

    });

});