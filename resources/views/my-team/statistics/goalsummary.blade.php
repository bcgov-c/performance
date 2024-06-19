<x-side-layout title="{{ __('Statistic and Reports - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Goal Summary 
        </h2> 
		@include('my-team.statistics.partials.tabs')
    </x-slot>

	<div class="d-flex justify-content-end mb-4 no-print">
        <a class="btn btn-primary" id="btn_print">Print</a>
    </div>

	<form id="filter-form" class="no-print">
		<input type="hidden" name="filter_params" value="{{ old('filter') }}">
		
		@include('my-team.statistics.partials.filter',['formaction' => route('my-team.statistics.goalsummary') ])

	</form>

<h1 class="mt-3 print-only">Shared Employees Summary</h1>
<h5 class="mb-3 print-only">Created on {{ \Carbon\Carbon::now()->format('M d, Y') }}</h5>	

	
<span id="pdf-output">

	<div class="row justify-content-center">
		@foreach($data as $type)
		<div class="col-sm-12 col-md-6 col-xl-3">
			<div class="card text-center text-secondary" >
				<div class="card-header border-0 px-5" >
					<div class="d-table" style="min-height: 4em;">
						<p class="card-text d-table-cell align-middle h6 font-weight-bold">Average Active {{ $type['name'] }} Goals Per Employee</p> 
					</div>
				</div>
				<div class="card-body pt-2 pb-4">
					<p class="card-text h2 text-primary">{{ number_format($type['average'],2) }}</p>
				</div>
			</div>
		</div>
		@endforeach
	</div>

	<p class="print-only page-break">&nbsp;</p>

	<div class="row justify-content-center">

		<div class="col-sm-12 col-md-10 col-xl-3">
			<div class="card">
				<div class="card-body">
					<div class=" chart has-fixed-height" id="pie_basic_1">
						Loading...
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-12 col-md-10 col-xl-3">
			<div class="card">
				<div class="card-body">
					<div class="chart has-fixed-height" id="pie_basic_2">
						Loading...
					</div>
				</div>
			</div>
		</div>

		<p class="print-only page-break">&nbsp;</p>

		<div class="col-sm-12 col-md-10 col-xl-3">
			<div class="card">
				<div class="card-body">
					<div class=" chart has-fixed-height" id="pie_basic_3">
						Loading...
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-12 col-md-10 col-xl-3">
			<div class="card">
				<div class="card-body">
					<div class=" chart has-fixed-height" id="pie_basic_4">
						Loading...
					</div>
				</div>
			</div>
		</div>
	
	</div>

	<p class="print-only page-break">&nbsp;</p>

	<div class="row justify-content-center">
		<div class="col-sm-10 ">
			<div class="card">
				<div class="card-body">
					<div class="bar-chart has-fixed-height" id="bar_basic_1">
						Loading...
					</div>
				</div>
			</div>
		</div>
	</div>

</span>


<x-slot name="css">
<style>

	@media screen {
		.chart {
			min-height:400px;
			/* min-width: 100px; */
		}
	
		.bar-chart {
			min-height:500px;
		}
	
		.print-only {
			display: none;
		}
	
	}
	
	@media print {
	
		@page { size:letter } 
		body { 
			max-width: 800px !important;
		}	 
		.no-print, .no-print *
		{
			display: none !important;
		}
		.chart {
			/* min-width:  180px;  */
			margin-left: 10% !important; 
		}	

		.bar-chart {
			margin-left: 0px !important; 
		}
	
		.row {
			display: block;
		}
		.page-break  { 
			display:block; 
			page-break-before : always; 
		}
		
	}
	
</style>
</x-slot>

<x-slot name="js">

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.3.2/echarts.min.js"></script>	
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.2.0/jspdf.umd.min.js"></script>
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script> --}}
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}

<script type="text/javascript">


$(function()  {
	
	var	pie_basic_1_data = {!!json_encode( $data[''] )!!};
	var	pie_basic_2_data = {!!json_encode( $data[1] )!!};
	var	pie_basic_3_data = {!!json_encode( $data[2] )!!};
	var	pie_basic_4_data = {!!json_encode( $data[3] )!!};

	var	bar_basic_1_data = {!!json_encode( $data_tag )!!};

	var allCharts = [];
	var export_url = '{{ route('my-team.statistics.goalsummary.export') }}';
	var tag_export_url = '{{ route('my-team.statistics.goalsummary.tag.export') }}'; 

	function createChart(divId, myData) {

		var myChart = echarts.init( document.getElementById( divId ) );
		allCharts.push(myChart);

		var array_ids = [];
		myData['groups'].forEach((element) => {
			  array_ids = array_ids.concat( element['ids'] );
		});

		option = {
			ids: array_ids,								// parameter for exporting
			goal_type_id: myData['goal_type_id'],		// parameter for exporting
			backgroundColor: "#fff",
			textStyle: {
				// fontFamily: 'Roboto, Arial, Verdana, sans-serif',
				fontSize: 12
			},
			title: {
				text: 'Active \n' + myData['name'] + ' Goals \nPer Employee',
				left: 'center',
				triggerEvent: true,
				textStyle: {
					fontSize: 15,
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
						title: 'Download to excel',
						icon: 'path//M224 136V0H24C10.7 0 0 10.7 0 24v464c0 13.3 10.7 24 24 24h336c13.3 0 24-10.7 24-24V160H248c-13.2 0-24-10.8-24-24zm60.1 106.5L224 336l60.1 93.5c5.1 8-.6 18.5-10.1 18.5h-34.9c-4.4 0-8.5-2.4-10.6-6.3C208.9 405.5 192 373 192 373c-6.4 14.8-10 20-36.6 68.8-2.1 3.9-6.1 6.3-10.5 6.3H110c-9.5 0-15.2-10.5-10.1-18.5l60.3-93.5-60.3-93.5c-5.2-8 .6-18.5 10.1-18.5h34.8c4.4 0 8.5 2.4 10.6 6.3 26.1 48.8 20 33.6 36.6 68.5 0 0 6.1-11.7 36.6-68.5 2.1-3.9 6.2-6.3 10.6-6.3H274c9.5-.1 15.2 10.4 10.1 18.4zM384 121.9v6.1H256V0h6.1c6.4 0 12.5 2.5 17 7l97.9 98c4.5 4.5 7 10.6 7 16.9z',
						onclick: function (option1) {
							ids =  myChart.getModel().option.ids;
							goal_id =  myChart.getModel().option.goal_type_id;
							filter = $('input[name=filter_params').val();
							
							// let _url = '{{ route('my-team.statistics.goalsummary.export')}}' 
							let _url = export_url + '?goal=' + goal_id + '&ids=' + ids + filter;
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
				data: ['0', '1-5','6-10','>10'],
				itemHeight: 8,
				itemWidth: 8,
				textStyle: {
					color: '#6c757d',
				}
			},
			series: [{
				name: myData['name'],
				type: 'pie',
				// radius: '50%',
				radius: ['15%', '50%'],
				center: ['50%', '50%'],
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
				data: myData['groups']
			}],
			
		};

		option && myChart.setOption(option);
		// myChart.setOption(option);

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
			// console.log(params.name + ' - '  + params.value + ' - ' +  ' - ' + params.data.goal_id) ;
			goal_type_id = myChart.getModel().option.goal_type_id;
			// let _url = '{{ route('my-team.statistics.goalsummary.export')}}' 
			//  let _url = '{{ route('my-team.statistics.goalsummary.export')}}' 
							// + '?goal=' + params.data.goal_id + '&range=' + params.name;
			filter = $('input[name=filter_params').val();
			let _url = export_url + '?goal=' + goal_type_id + '&range=' + params.name  + '&ids=' + params.data.ids + filter;
      		window.location.href = _url;


		});

	}

	function createBarChart(divId, myData) {

		var myChart = echarts.init( document.getElementById( divId ) );
		allCharts.push(myChart);

		option = {
			title: {
				// text: myData['name'],
				subtext: myData['name']
			},
			xAxis: {
				type: 'value',
				boundaryGap: [0, 1],
			},
			yAxis: {
				type: 'category',
				inverse: true,
				data: myData['labels'],
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
						title: 'Download to excel',
						icon: 'path//M224 136V0H24C10.7 0 0 10.7 0 24v464c0 13.3 10.7 24 24 24h336c13.3 0 24-10.7 24-24V160H248c-13.2 0-24-10.8-24-24zm60.1 106.5L224 336l60.1 93.5c5.1 8-.6 18.5-10.1 18.5h-34.9c-4.4 0-8.5-2.4-10.6-6.3C208.9 405.5 192 373 192 373c-6.4 14.8-10 20-36.6 68.8-2.1 3.9-6.1 6.3-10.5 6.3H110c-9.5 0-15.2-10.5-10.1-18.5l60.3-93.5-60.3-93.5c-5.2-8 .6-18.5 10.1-18.5h34.8c4.4 0 8.5 2.4 10.6 6.3 26.1 48.8 20 33.6 36.6 68.5 0 0 6.1-11.7 36.6-68.5 2.1-3.9 6.2-6.3 10.6-6.3H274c9.5-.1 15.2 10.4 10.1 18.4zM384 121.9v6.1H256V0h6.1c6.4 0 12.5 2.5 17 7l97.9 98c4.5 4.5 7 10.6 7 16.9z',
						onclick: function (option1) {
							// ids =  myChart.getModel().option.ids;
							// goal_id =  myChart.getModel().option.goal_type_id;
							filter = $('input[name=filter_params').val();
							
							// let _url = '{{ route('my-team.statistics.goalsummary.export')}}' 
							let _url = tag_export_url + '?tag=' + filter;
      						window.location.href = _url;
						}
					},
				}
			},
			tooltip: {
				trigger: 'axis',
				axisPointer: {
					type: 'shadow',
				},
				formatter: function (params) {
					var tar = params[0];
					return 'Tag : ' + tar.name + '<br/>Count : ' + tar.value;
				}	
			},
			grid: {
				left: '3%',
				right: '4%',
				bottom: '10%',
				containLabel: true
			},
			series: [
				{
					// name: '2011',
					type: 'bar',
					data: myData['values'],
					showBackground: true,
					backgroundStyle: {
						color: 'rgba(180, 180, 180, 0.2)'
					}
				
				},
			],
			
		};

		option && myChart.setOption(option);

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
			// console.log(params.name + ' - '  + params.value + ' - ' +  ' - ' + params.data.goal_id) ;
			// goal_type_id = myChart.getModel().option.goal_type_id;
			console.log(params.name );
			console.log(params.value );
			console.log(params.id );
			// let _url = '{{ route('my-team.statistics.goalsummary.export')}}' 
			//  let _url = '{{ route('my-team.statistics.goalsummary.export')}}' 
							// + '?goal=' + params.data.goal_id + '&range=' + params.name;
			filter = $('input[name=filter_params').val();
			let _url = tag_export_url + '?tag=' + params.name  + filter;
			window.location.href = _url;

		});


	}

	// call function to create chart 
	createChart('pie_basic_1', pie_basic_1_data);
	createChart('pie_basic_2', pie_basic_2_data);
	createChart('pie_basic_3', pie_basic_3_data);
	createChart('pie_basic_4', pie_basic_4_data);

	// Tag chart 
	createBarChart('bar_basic_1', bar_basic_1_data);


	// Trigger when window resize
	window.onresize = function() {
		allCharts.forEach((chart) => { chart.resize(); })
	};				
	
	// Printing 
	const btnExportHTML = document.getElementById("btn_print");
	btnExportHTML.addEventListener("click", async () => {        
		window.print();
		return;

		// var element = document.getElementById('pdf-output');
		// var opt = {
		// 	margin:       0.2,
		// 	filename:     'chart.pdf',
		// 	image:        { type: 'jpeg', quality: 0.98 },
		// 	html2canvas:  { scale: 0.8 },
		// 	jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
		// };

		// // New Promise-based usage:
		// html2pdf().set(opt).from(element).save();
	
	

	// 	try {
	// 		const doc = new jspdf.jsPDF({
	// 		unit: "px",
	// 		orientation: "p",
	// 		hotfixes: ["px_scaling"]
	// 	});
	// 	await doc
	// 	.html(document.querySelector("#pdf-output"), {
	// 		filename: "charts.pdf",
	// 		// callback: function (doc) {
	// 		//   doc.save('charts.pdf');
	// 		// },
	// 		html2canvas: {
	// 		allowTaint: true,
	// 		backgroundColor: "transparent",
	// 		// scale: 0.8, 
	// 		// logging: false
	// 		}
	// 	})
	// 	.save();

	// 	// another way:
	// 	// const canvas = await html2canvas(document.querySelector(".chart-box"))
	// 	// const img = await loadImage(canvas.toDataURL())
	// 	// const dpr = chart1.getDevicePixelRatio()
	// 	// doc.addImage(img.src, 'PNG', 0, 0, img.width / dpr, img.height / dpr)
	// 	// await doc.save()
	// } catch (e) {
	// 	console.error("failed to export", e);
	// }
	});


});

</script>

</x-slot>


</x-side-layout>
