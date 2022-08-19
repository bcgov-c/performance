<x-side-layout title="{{ __('Statistic and Reports - Performance Development Platform') }}">
	<x-slot name="header">
		<h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
			Shared Employees Summary 
		</h2> 
		@include('hradmin.statistics.partials.tabs')
	</x-slot>

	<div class="d-flex justify-content-end mb-4 no-print">
		<a class="btn btn-primary mr-3" id="btn_print">Print</a>
	</div>

	<form id="filter-form" class="no-print">
		<input type="hidden" name="filter_params" value="{{ old('filter') }}">
		
		@include('hradmin.statistics.partials.filter',['formaction' => route('hradmin.statistics.sharedsummary') ])

	</form>

<h1 class="mt-3 print-only">Shared Employees Summary</h1>
<h5 class="mb-3 print-only">Created on {{ \Carbon\Carbon::now()->format('M d, Y') }}</h5>
	
<span id="pdf-output">

	<div class="row justify-content-center">
		<div class="col-sm-12 col-md-10 col-lg-6">
			<div class="card">
				<div class="card-body">
					<div class="chart has-fixed-height" id="pie_basic_1">
					Loading...
					</div>
				</div>
			</div>
		</div>
	</div>
</span>


<x-slot name="css">
	<style>
		@media screen  {
		.chart {
			/* min-width:  180px;  */
			min-height: 480px;
		}	
	
		.print-only {
			display: none;
		}
	}	
	
	@media print {
	
		@page { size:letter } 
		body { 
			max-width: 800px !important;
			margin-left: 100px !important;
		}	 
		.no-print, .no-print *
		{
			display: none !important;
		}
		.chart {
			/* min-width:  180px;  */
			margin-left: 60px; 
		}	
	
		.row {
			display: block;
		}
		.page-break  { 
			display:block; 
			page-break-before : always ; 
	
		}
		  
	}
	</style>
</x-slot>

<x-slot name="js">

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.3.2/echarts.min.js"></script>	

<script type="text/javascript">

$(function() {
	
	var	pie_basic_1_data = {!!json_encode( $data['chart1'] )!!};

	var allCharts = [];
	var export_url = '{{ route('hradmin.statistics.sharedsummary.export') }}';

	function createChart(divId, myData) {

		// Create New Chart
		var myChart = echarts.init( document.getElementById( divId ) );
		allCharts.push(myChart);

		var array_ids = [];
		myData['groups'].forEach((element) => {
				array_ids = array_ids.concat( element['ids'] );
		});
		
		option = {
			ids : array_ids,					// parameter for exporting
			chart_id : myData['chart_id'],		// parameter for exporting
			backgroundColor: "#fff",
			textStyle: {
				fontSize: 12
			},
			title: {
				text: myData['title'],
				left: 'center',
				triggerEvent: true,
				textStyle: {
					fontSize: 20,
					fontWeight: 1000,
					color: '#6c757d',
				},
				subtextStyle: {
					fontSize: 12
				},
			},
			toolbox: {
				show: true,
				bottom: -5,
				feature: {
					// mark: { show: true },
					dataView: { show: true, readOnly: true },
					// restore: { show: true },
					// saveAsImage: { show: true },
					myTool1: {
						show: true,
						title: 'download to excel',
						icon: 'path//M224 136V0H24C10.7 0 0 10.7 0 24v464c0 13.3 10.7 24 24 24h336c13.3 0 24-10.7 24-24V160H248c-13.2 0-24-10.8-24-24zm60.1 106.5L224 336l60.1 93.5c5.1 8-.6 18.5-10.1 18.5h-34.9c-4.4 0-8.5-2.4-10.6-6.3C208.9 405.5 192 373 192 373c-6.4 14.8-10 20-36.6 68.8-2.1 3.9-6.1 6.3-10.5 6.3H110c-9.5 0-15.2-10.5-10.1-18.5l60.3-93.5-60.3-93.5c-5.2-8 .6-18.5 10.1-18.5h34.8c4.4 0 8.5 2.4 10.6 6.3 26.1 48.8 20 33.6 36.6 68.5 0 0 6.1-11.7 36.6-68.5 2.1-3.9 6.2-6.3 10.6-6.3H274c9.5-.1 15.2 10.4 10.1 18.4zM384 121.9v6.1H256V0h6.1c6.4 0 12.5 2.5 17 7l97.9 98c4.5 4.5 7 10.6 7 16.9z',
						onclick: function (option1){
							ids =  myChart.getModel().option.ids;
							chart_id = myChart.getModel().option.chart_id;
							filter = $('input[name=filter_params').val();

							let _url = export_url + '?chart=' + chart_id + filter; //  + '&ids=' + ids;
							window.location.href = _url;
						}
					},
				}
			},

			// tooltip: {
			// 	trigger: 'item',
			// 	// backgroundColor: 'rgba(0,0,0,0.8)',
			// 	padding: [20, 25],
			// 	textStyle: {
			// 	// 	color: "#fff",
			// 	// 	fontSize: 12,
			// 		fontFamily: 'Roboto, sans-serif'
			// 	},
			// 	formatter: "{b}:<br/>{c} ({d}%)"
			// },

			legend: {
				orient: 'horizontal',
				bottom: '10%',
				left: 'center',
				data: myData['legend'], // ['0', '1-5','6-10','>10'],
				itemHeight: 8,
				itemWidth: 8,
				textStyle: {
					color: '#6c757d',
				}
			},
			series: [{
				name: myData['title'],
				type: 'pie',
				// radius: '50%',
				radius: ['15%', '60%'],
				center: ['50%', '45%'],
				
				itemStyle: {
					normal: {
						borderWidth: 1,
						borderColor: '#fff'
					}
				},
				label: {
					alignTo: 'edge',
					formatter: '{name|{b}}\n{value|{c}}  {per|{d}%}',
					minMargin: 1,
					fontWeight: 'bold',
					edgeDistance: 3,
					lineHeight: 20,
					// verticalAlign: 'bottom',
					rich: {
						name: {
						},
						value: {
							fontWeight: 'normal',
							fontSize: 12,
							color: '#999',
						},
						per: {
							color: '#fff',
							backgroundColor: '#888888', //'#4C5058',
							padding: [2, 4],
							borderRadius: 4,
							fontSize: 10,
							verticalAlign: 'middle',
						}
					}
				},
				labelLine: {
					length: 15,
					length2: 0,
					maxSurfaceAngle: 80
				},
				labelLayout: function (params) {
					const isLeft = params.labelRect.x < myChart.getWidth() / 2;
					const points = params.labelLinePoints;
					// Update the end point.
					points[2][0] = isLeft
						? params.labelRect.x
						: params.labelRect.x + params.labelRect.width;
					return {
						labelLinePoints: points
					};
				},
				emphasis: {
					itemStyle: {
					shadowBlur: 10,
					shadowOffsetX: 0,
					shadowColor: 'rgba(0, 0, 0, 0.5)'
					}
				},
				data: myData['groups'],
			}],
			
		};

		option && myChart.setOption(option);

		// Trigger when the clcik on the chart segment 
		myChart.on('click', function(params) {

			if (params.componentType === 'title') {
					console.log('title is clicked!')
				return;
				}

			if (params.componentType === 'series') {
				console.log( 'series  clicked' ) ;
				if (params.seriesType === 'edge') {
					console.log( 'edge clicked' ) ;
				}
			} 

			// prepare the parameters for calling export on difference segments
			chart_id = myChart.getModel().option.chart_id;
			filter = $('input[name=filter_params').val();
			let _url = export_url + '?chart=' + chart_id + '&legend=' + params.data.legend + filter;
			window.location.href = _url;

		});

	}

	// Call fundtion to create a new chart
	createChart('pie_basic_1', pie_basic_1_data);
	
	
	// trigger: resize the chart when the windows resize
	window.onresize = function() {
		allCharts.forEach((chart) => { chart.resize(); })
	};				
	
	// Printing
	const btnExportHTML = document.getElementById("btn_print");
	btnExportHTML.addEventListener("click", async () => {        
		window.print();
		return;
	});

});

</script>

</x-slot>

</x-side-layout>

