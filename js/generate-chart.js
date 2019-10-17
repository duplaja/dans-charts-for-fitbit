/* Dan's Charts for Fitbit - JS */	
jQuery(document).ready(function($) {

    var number_of_times = passed_data_1.number_of_times;

    //console.log(number_of_times);

    var colorone = "#2196f3"; //Nonfasting
    var colortwo = "#4fa861"; //Fasting

    
    var cur_data_array;
    var values;
    var labels;
    var dataset_label;
    var chart_title_text;
    var is_adf;
    var charttype;
    var canvas_id;
    var legend_id;
    var stepped;

    var z;
    for(z=1; z<=number_of_times; z++) {

        if(z == 1) {
            cur_data_array = passed_data_1; 
        } 
        else if (z == 2) {
            cur_data_array = passed_data_2;
        }
        else if (z == 3) {
            cur_data_array = passed_data_3;
        }
        else if (z == 4) {
            cur_data_array = passed_data_4;
        }
        else if (z == 5) {
            cur_data_array = passed_data_5;
        }
        else if (z == 6) {
            cur_data_array = passed_data_6;
        }
        else if (z == 7) {
            cur_data_array = passed_data_7;
        }
        else if (z == 8) {
            cur_data_array = passed_data_8;
        }
        else if (z == 9) {
            cur_data_array = passed_data_9;
        }
        else if (z == 10) {
            cur_data_array = passed_data_10;
        }
        else if (z == 11) {
            cur_data_array = passed_data_11;
        }
        else if (z == 12) {
            cur_data_array = passed_data_12;
        }
        else if (z == 13) {
            cur_data_array = passed_data_13;
        }
        else if (z == 14) {
            cur_data_array = passed_data_14;
        }
        else if (z == 15) {
            cur_data_array = passed_data_15;
        }
        else if (z == 16) {
            cur_data_array = passed_data_16;
        }
        else if (z == 17) {
            cur_data_array = passed_data_17;
        }
        else if (z == 18) {
            cur_data_array = passed_data_18;
        }
        else if (z == 19) {
            cur_data_array = passed_data_19;
        }
        else if (z == 20) {
            cur_data_array = passed_data_20;
        }
        
        values = JSON.parse(cur_data_array.values);
        labels = JSON.parse(cur_data_array.dates);
        dataset_label = cur_data_array.dataset_label;
        chart_title_text = cur_data_array.chart_title;
        is_adf = cur_data_array.is_adf;
        charttype = cur_data_array.graph_type;
        canvas_id = cur_data_array.canvas_id;
        legend_id = cur_data_array.legend_id;

        stepped = cur_data_array.stepped;

        console.log(values);

        ctx = document.getElementById(canvas_id).getContext('2d');

        pointBackgroundColors = [];
        myChart = new Chart(ctx, {
            type: charttype,
            data: {
                labels: labels,
                datasets: [{
                    label: dataset_label, // Name the series
                    data: values,
                    pointBackgroundColor: pointBackgroundColors,
                    pointBorderColor: pointBackgroundColors,
                    fill: false,
                    borderColor: '#2196f3', // Add custom color border (Line)
                    backgroundColor: '#2196f3', // Add custom color background (Points and Fill)
                    borderWidth: 1.5, // Specify bar border width
                    steppedLine: stepped
                }]},
            options: {
            responsive: true, // Instruct chart js to respond nicely.
            maintainAspectRatio: false, // Add to prevent default behaviour of full-width/height
            scales: {
                yAxes: [{
                    display: true,
                    ticks: {
                        //suggestedMin: 200,    // minimum will be 0, unless there is a lower value.
                        // OR //
                        //beginAtZero: true   // minimum value will be 0.
                    }
                }]
            },
            title: {
                    display: true,
                    text: chart_title_text,
                    fontSize: 22	
            },
            legendCallback: function(chart) {
                    return '<ul><li style="color:'+colortwo+';font-weight:800">After Fasting Day</li><li style="color:'+colorone+';font-weight:800">After Non-Fasting Day</li></ul>';
            } 
            },
        });
        if (is_adf == 'yes') {
            for (i = 0; i < myChart.data.datasets[0].data.length; i++) {
        
                if (i%2 == 0) {
                    pointBackgroundColors.push(colorone);
                } else {
                    pointBackgroundColors.push(colortwo);
                }
            }
            myChart.update();
            document.getElementById(legend_id).innerHTML = myChart.generateLegend();
        }

    }

    
});