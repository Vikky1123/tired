"use strict";

// Mock data for the chart
const mockData = [
    {
        x: new Date('2024-04-01').getTime(),
        y: [45000, 46000, 44800, 45500]
    },
    {
        x: new Date('2024-04-02').getTime(),
        y: [45500, 47000, 45200, 46800]
    },
    {
        x: new Date('2024-04-03').getTime(),
        y: [46800, 48000, 46500, 47500]
    },
    {
        x: new Date('2024-04-04').getTime(),
        y: [47500, 49000, 47100, 48300]
    },
    {
        x: new Date('2024-04-05').getTime(),
        y: [48300, 48900, 47800, 48600]
    },
    {
        x: new Date('2024-04-06').getTime(),
        y: [48600, 49500, 48200, 49300]
    },
    {
        x: new Date('2024-04-07').getTime(),
        y: [49300, 50000, 48900, 49800]
    },
    {
        x: new Date('2024-04-08').getTime(),
        y: [49800, 50500, 49500, 50200]
    }
];

const chartOptions = {
    series: [{
        name: 'BTC/USDT',
        data: mockData
    }],
    chart: {
        type: 'candlestick',
        height: 400,
        background: 'transparent',
        theme: {
            mode: 'dark'
        }
    },
    xaxis: {
        type: 'datetime',
        labels: {
            style: {
                colors: '#fff'
            },
            format: 'MMM dd'
        }
    },
    yaxis: {
        tooltip: {
            enabled: true
        },
        labels: {
            style: {
                colors: '#fff'
            },
            formatter: function(value) {
                return '$' + value.toFixed(0);
            }
        }
    },
    grid: {
        borderColor: '#2c2c2c',
        strokeDashArray: 3
    },
    plotOptions: {
        candlestick: {
            colors: {
                upward: '#00EC42',
                downward: '#FF2E2E'
            }
        }
    },
    tooltip: {
        theme: 'dark',
        x: {
            format: 'MMM dd HH:mm'
        }
    },
    title: {
        text: 'BTC/USDT',
        align: 'left',
        style: {
            color: '#fff',
            fontSize: '16px'
        }
    }
};

// Initialize chart
document.addEventListener('DOMContentLoaded', function() {
    const chart = new ApexCharts(document.querySelector("#cryptoChart"), chartOptions);
    chart.render();
    
    // Handle coin selection to update chart title
    document.querySelectorAll('input[name="cryptoChart"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const symbol = this.id.replace('Chart', '').toUpperCase();
            chart.updateOptions({
                title: {
                    text: `${symbol}/USDT`
                }
            });
        });
    });
}); 