document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('ordersChart');
    if (!ctx) return;

    new Chart(ctx.getContext('2d'), {
        type: 'pie',
        data: {
            labels: ['Aprovados', 'Pendentes'],
            datasets: [{
                data: [
                    parseInt(ctx.dataset.approved), 
                    parseInt(ctx.dataset.pending)
                ],
                backgroundColor: ['#68C72E', '#E04A4A'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.parsed;
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${context.label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});