// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

function number_format(number, decimals, dec_point, thousands_sep) {
  // *     example: number_format(1234.56, 2, ',', ' ');
  // *     return: '1 234,56'
  number = (number + '').replace(',', '').replace(' ', '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
      var k = Math.pow(10, prec);
      return '' + Math.round(n * k) / k;
    };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }
  return s.join(dec);
}


let userBarLabels= []; let userBarHours = [];let userBarTasks = [];
jQuery.each( timeReport['user_hours'], function( index, value ){
    userBarLabels.push(value['label']);
    userBarHours.push(value['total_hours']);
    userBarTasks.push(value['total_tasks']);

});

let userBarColors = [
    "rgba(255, 99, 132, 0.2)",
    "rgba(255, 159, 64, 0.2)",
    "rgba(255, 205, 86, 0.2)",
    "rgba(75, 192, 192, 0.2)",
    "rgba(54, 162, 235, 0.2)",
    "rgba(60, 180, 75, 0.2 )",
    "rgba(230, 25, 75, 0.2 )",
    "rgba(255, 225, 25, 0.2 )",
    "rgba(0, 130, 200, 0.2 )",
    "rgba(245, 130, 48, 0.2 )",
    "rgba(145, 30, 180, 0.2 )",
    "rgba(70, 240, 240, 0.2 )",
    "rgba(240, 50, 230, 0.2 )",
    "rgba(210, 245, 60, 0.2 )",
    "rgba(250, 190, 212, 0.2 )",
    "rgba(0, 128, 128, 0.2 )",
    "rgba(220, 190, 255, 0.2 )",
    "rgba(170, 110, 40, 0.2 )",
    "rgba(255, 250, 200, 0.2 )",
    "rgba(128, 0, 0, 0.2 )",
    "rgba(170, 255, 195, 0.2 )",
    "rgba(128, 128, 0, 0.2 )",
    "rgba(255, 215, 180, 0.2 )",
    "rgba(0, 0, 128, 0.2 )",
    "rgba(128, 128, 128, 0.2 )",
    "rgba(255, 255, 255, 0.2 )",
    "rgba(0, 0, 0, 0.2)"
];
let userBarBorderColors = [
    "rgba(255, 99, 132)",
    "rgba(255, 159, 64)",
    "rgba(255, 205, 86)",
    "rgba(75, 192, 192)",
    "rgba(54, 162, 235)",
    "rgba(60, 180, 75)",
    "rgba(230, 25, 75)",
    "rgba(255, 225, 25)",
    "rgba(0, 130, 200)",
    "rgba(245, 130, 48)",
    "rgba(145, 30, 180)",
    "rgba(70, 240, 240)",
    "rgba(240, 50, 230)",
    "rgba(210, 245, 60)",
    "rgba(250, 190, 212)",
    "rgba(0, 128, 128)",
    "rgba(220, 190, 255)",
    "rgba(170, 110, 40)",
    "rgba(255, 250, 200)",
    "rgba(128, 0, 0)",
    "rgba(170, 255, 195)",
    "rgba(128, 128, 0)",
    "rgba(255, 215, 180)",
    "rgba(0, 0, 128)",
    "rgba(128, 128, 128)",
    "rgba(255, 255, 255)",
    "rgba(0, 0, 0, 0.2)"
];


new Chart(document.getElementById("userHoursBarChart"),{
    "type":"horizontalBar",
    "data":{
        "labels": userBarLabels,
        "datasets":[{
            "label": " hours",
            "data": userBarHours,
            "fill":false,
            "backgroundColor": userBarColors,
            "borderColor":userBarBorderColors,
            "borderWidth":1
        }]
    },
    "options":{
        "scales":{
            "xAxes":[{
                "ticks":{
                    "beginAtZero":true
                }
            }]
        },
        legend: {
            display: false
        },
        maintainAspectRatio: false
    }
});
