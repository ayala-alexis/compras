<script type="text/javascript">
var chart;
$(document).ready(function() {
	var f = new Date();
	//f.getDate() + "/" + (f.getMonth() +1) + "/" + f.getFullYear()
	var options = {
		chart: {
			renderTo: 'container1',
			type: 'line'
		},
		title: {
			text: 'solicitudes propias realizadas '+f.getFullYear()
		},
		xAxis: {
			categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
			allowDecimals: false,
			labels: {
                rotation: 90
            }
		},
		yAxis: {
			title: {
				text: 'solicitudes'
			},
			allowDecimals: false
		},
		tooltip: {
			enabled: false,
			formatter: function() {
					return this.series.name +' : <b>'+ this.y + '</b>';
			}
		},
		plotOptions: {
            line: {
                dataLabels: {
                    enabled: true
                },
                enableMouseTracking: false
            }
        },
		series: [
		 		{
        	    	name: 'total'
        		},
        		{
            		name: 'pre-solicitudes'
        		},
        		{
            		name: 'solicitudes'
        		},
        		{
            		name: 'pre-requisiciones'
        		},
        		{
            		name: 'requisiciones'
        		}
                ]
		/*
		series: [{
			name: 'cantidad'
		}]*/
	}
	// Load data asynchronously using jQuery. On success, add the data
	// to the options and initiate the chart.
	// This data is obtained by exporting a GA custom report to TSV.
	// http://api.jquery.com/jQuery.get/
	$.get('data.php', null, function(tsv) {
		meses = [];
		cuantas = [];
		presol = [];
		solicitudes = [];
		prereq = [];
		requisiciones = [];
		try {
			// split the data return into lines and parse them
			tsv = tsv.split(/\n/g);
			$.each(tsv, function(i, line) {
				line = line.split(/\t/);
				cantidad = parseInt(line[1].replace(',', ''), 10);
				presol_cant = parseInt(line[2].replace(',', ''), 10);
				solicitudes_cant = parseInt(line[3].replace(',', ''), 10);
				prereq_cant = parseInt(line[4].replace(',', ''), 10);
				requisiciones_cant = parseInt(line[5].replace(',', ''), 10);
				cuantas.push(cantidad);
				presol.push(presol_cant);
				meses.push([line[0]]);
				solicitudes.push(solicitudes_cant);
				prereq.push(prereq_cant);
				requisiciones.push(requisiciones_cant);
			});
		} catch (e) {
			//alert(e.message);
		};
		options.series[0].data = cuantas;
		options.series[1].data = presol;
		options.series[2].data = solicitudes;
		options.series[3].data = prereq;
		options.series[4].data = requisiciones;
		//options.xAxis.categories = meses;
		chart = new Highcharts.Chart(options);
	});
/*
	var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'container2'
            },
            title: {
                text: 'Combination chart'
            },
            xAxis: {
                categories: ['Apples', 'Oranges', 'Pears', 'Bananas', 'Plums']
            },
            tooltip: {
                formatter: function() {
                    var s;
                    if (this.point.name) { // the pie chart
                        s = ''+
                            this.point.name +': '+ this.y +' fruits';
                    } else {
                        s = ''+
                            this.x  +': '+ this.y;
                    }
                    return s;
                }
            },
            labels: {
                items: [{
                    html: 'Total fruit consumption',
                    style: {
                        left: '40px',
                        top: '8px',
                        color: 'black'
                    }
                }]
            },
            series: [{
                type: 'column',
                name: 'Jane',
                data: [3, 2, 1, 3, 4]
            }, {
                type: 'column',
                name: 'John',
                data: [2, 3, 5, 7, 6]
            }, {
                type: 'column',
                name: 'Joe',
                data: [4, 3, 3, 9, 0]
            }, {
                type: 'spline',
                name: 'Average',
                data: [3, 2.67, 3, 6.33, 3.33],
                marker: {
                	lineWidth: 2,
                	lineColor: Highcharts.getOptions().colors[3],
                	fillColor: 'white'
                }
            }, {
                type: 'pie',
                name: 'Total consumption',
                data: [{
                    name: 'Jane',
                    y: 13,
                    color: '#4572A7' // Jane's color
                }, {
                    name: 'John',
                    y: 23,
                    color: '#AA4643' // John's color
                }, {
                    name: 'Joe',
                    y: 19,
                    color: '#89A54E' // Joe's color
                }],
                center: [100, 80],
                size: 100,
                showInLegend: false,
                dataLabels: {
                    enabled: false
                }
            }]
        });
    });
    var chart;
    $(document).ready(function() {

    	// Radialize the colors
		Highcharts.getOptions().colors = $.map(Highcharts.getOptions().colors, function(color) {
		    return {
		        radialGradient: { cx: 0.5, cy: 0.3, r: 0.7 },
		        stops: [
		            [0, color],
		            [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
		        ]
		    };
		});

		// Build the chart
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'container3',
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Browser market shares at a specific website, 2010'
            },
            tooltip: {
        	    pointFormat: '{series.name}: <b>{point.percentage}%</b>',
            	percentageDecimals: 1
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        formatter: function() {
                            return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %';
                        }
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Browser share',
                data: [
                    ['Firefox',   45.0],
                    ['IE',       26.8],
                    {
                        name: 'Chrome',
                        y: 12.8,
                        sliced: true,
                        selected: true
                    },
                    ['Safari',    8.5],
                    ['Opera',     6.2],
                    ['Others',   0.7]
                ]
            }]
        });
    });
    var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'container4',
                type: 'bar'
            },
            title: {
                text: 'Historic World Population by Region'
            },
            subtitle: {
                text: 'Source: Wikipedia.org'
            },
            xAxis: {
                categories: ['Africa', 'America', 'Asia', 'Europe', 'Oceania'],
                title: {
                    text: null
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Population (millions)',
                    align: 'high'
                },
                labels: {
                    overflow: 'justify'
                }
            },
            tooltip: {
                formatter: function() {
                    return ''+
                        this.series.name +': '+ this.y +' millions';
                }
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -100,
                y: 100,
                floating: true,
                borderWidth: 1,
                backgroundColor: '#FFFFFF',
                shadow: true
            },
            credits: {
                enabled: false
            },
            series: [{
                name: 'Year 1800',
                data: [107, 31, 635, 203, 2]
            }, {
                name: 'Year 1900',
                data: [133, 156, 947, 408, 6]
            }, {
                name: 'Year 2008',
                data: [973, 914, 4054, 732, 34]
            }]
        });
    });
	*/

});
</script>
<!-- <div id="chart1" style="width: 410px; margin-left: 17px; height: 200px; float: left; z-index: 1"></div> -->
<div class="row-fluid">
	<div id="container1" class="span12" style="height: 300px; z-index: 1"></div>
</div>
<div class="row-fluid">
	<div id="container2" class="span6" style="height: 300px; z-index: 1"></div>
	<div id="container3" class="span6" style="height: 300px; z-index: 1"></div>
</div>
<div class="row-fluid">
	<div id="container4" class="span6" style="height: 300px; z-index: 1"></div>
	<div id="container5" class="span6" style="height: 300px; z-index: 1"></div>
</div>