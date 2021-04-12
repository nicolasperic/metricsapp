// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';


//console.log(jQuery('#usTypeCount'));
//console.log('chart-pie-demo.js');
//console.log(percentages);

if (typeof timeReport !== 'undefined' && typeof percentages!== 'undefined') {


    //datasets
    let countPercent = [];
    let hoursPercent = [];
    //labels
    let usTypesLabels = [];
    //colors for datasets
    let backgroundColor = [];
    let hoverColor = [];
    //info for labels
    let countTotal = [];
    let hoursTotal = [];

    jQuery.each(percentages, function (index, value) {
        usTypesLabels.push(value['label']);
        countPercent.push(value['count_percentage']);
        countTotal.push(value['total']);
        hoursPercent.push(value['hours_percentage']);
        hoursTotal.push(value['total_invested_hours']);
        backgroundColor.push(value['color']['main']);
        hoverColor.push(value['color']['hover']);

        //console.log(value);

        //console.log(value['label']+" "+value['total']+" "+value['count_percentage']+" "+value['total_invested_hours']+" "+value['hours_percentage']);
    });
//console.log(countTotal);
//console.log(labels);
//console.log(values);
// Pie Chart - US Types count/total %
    var ctx = document.getElementById("usTypeCount");
    var usTypeCount = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: usTypesLabels,//["Bugs", "Support", "Requirement"],
            datasets: [{
                label: 'Porcentaje',
                data: countPercent,//[55, 30, 15],//percentages
                backgroundColor: backgroundColor,//['#e74a3b', '#1cc88a', '#36b9cc', '#4e73df', '#f6c23e', '#c81cbf'],
                hoverBackgroundColor: hoverColor,//['#c22819', '#17a673', '#2c9faf', '#3b5399', '#cea334', '#871381'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }, {
                label: 'Horas',
                data: hoursPercent,//[55, 30, 15],//percentages
                backgroundColor: backgroundColor,//['#e74a3b', '#1cc88a', '#36b9cc', '#4e73df', '#f6c23e', '#c81cbf'],
                hoverBackgroundColor: hoverColor,//['#c22819', '#17a673', '#2c9faf', '#3b5399', '#cea334', '#871381'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
                callbacks: {
                    label: function (tooltipItem, chart) {
                        //console.log(chart);

                        let piePiece = number_format(chart.datasets[tooltipItem.datasetIndex].data[tooltipItem.index], 2);
                        let realValue;

                        //dataset 0 es % > countPercent unidad/total
                        //dataset 1 es horas horas/horasTotaltes
                        if (tooltipItem.datasetIndex == 0) {
                            realValue = countTotal[tooltipItem.index] + " user stories";
                        } else if (tooltipItem.datasetIndex == 1) {
                            realValue = hoursTotal[tooltipItem.index] + " horas";
                        }


                        var datasetLabel = chart.labels[tooltipItem.index] || '';
                        return datasetLabel + ': ' + piePiece + "% " + "(" + realValue + ")";
                    }
                }
            },
            legend: {
                display: false
            },
            cutoutPercentage: 30,
        },
    });
}