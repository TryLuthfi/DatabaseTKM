/* global Chart:false */

$(function () {
  'use strict'

  var ticksStyle = {
    fontColor: '#495057',
    fontStyle: 'bold'
  }

  var mode = 'index'
  var intersect = true

  var $rkapChartBar = $('#rkap_chart_bar')
  // eslint-disable-next-line no-unused-vars
  var rkapChartBar = new Chart($rkapChartBar, {
    type: 'bar',
    data: {
      labels: ['BP. ADHI', 'BP. WARDANI', 'BP. YAYA', 'BP. ZAENUL'],
      datasets: [
        {
          backgroundColor: '#007bff',
          borderColor: '#007bff',
          data: [345000000000, 180000000000, 95000000000, 30000000000]
        },
        {
          backgroundColor: '#89f336',
          borderColor: '#89f336',
          data: [180000000000,10000000000,56000000000,12000000000]
        },
        {
          backgroundColor: '#ff5c00',
          borderColor: '#ff5c00',
          data: [100000000000,7000000000,20000000000,4000000000]
        }
      ]
    },
    options: {
      maintainAspectRatio: false,
      tooltips: {
        mode: mode,
        intersect: intersect
      },
      hover: {
        mode: mode,
        intersect: intersect
      },
      legend: {
        display: false
      },
      scales: {
        yAxes: [{
          // display: false,
          gridLines: {
            display: true,
            lineWidth: '4px',
            color: 'rgba(0, 0, 0, .2)',
            zeroLineColor: 'transparent'
          },
          ticks: $.extend({
            beginAtZero: true,


            // Include a dollar sign in the ticks
            callback: function (value) {
              if(value % 1 === 0){
                value += ''

              return '' + value
            }

            }
          }, ticksStyle)
        }],
        xAxes: [{
          display: true,
          gridLines: {
            display: false
          },
          ticks: ticksStyle
        }]
      }
    }
  })

  var $rkapChatLine = $('#rkap_chart_line')
  // eslint-disable-next-line no-unused-vars
  var rkapChatLine = new Chart($rkapChatLine, {
    data: {
      labels: ['BP. ADHI', 'BP. WARDANI', 'BP. YAYA', 'BP. ZAENUL'],
      datasets: [{ // TARGET RKAP
        type: 'line',
        data: [345000000000, 180000000000, 95000000000, 30000000000],
        backgroundColor: 'transparent',
        borderColor: '#007bff',
        pointBorderColor: '#007bff',
        pointBackgroundColor: '#007bff',
        fill: false
        // pointHoverBackgroundColor: '#007bff',
        // pointHoverBorderColor    : '#007bff'
      },
      { // ACHIEVED PO
        type: 'line',
        data: [180000000000,10000000000,56000000000,12000000000],
        backgroundColor: 'tansparent',
        borderColor: '#89f336',
        pointBorderColor: '#89f336',
        pointBackgroundColor: '#89f336',
        fill: false
        // pointHoverBackgroundColor: '#ced4da',
        // pointHoverBorderColor    : '#ced4da'
      },
      { // ACHIEVED INVOICE
        type: 'line',
        data: [0,0,0,0],
        backgroundColor: 'tansparent',
        borderColor: '#ff5c00',
        pointBorderColor: '#ff5c00',
        pointBackgroundColor: '#ff5c00',
        fill: false
        // pointHoverBackgroundColor: '#ced4da',
        // pointHoverBorderColor    : '#ced4da'
      }]
    },
    options: {
      maintainAspectRatio: false,
      tooltips: {
        mode: mode,
        intersect: intersect
      },
      hover: {
        mode: mode,
        intersect: intersect
      },
      legend: {
        display: false
      },
      scales: {
        yAxes: [{
          // display: false,
          gridLines: {
            display: true,
            lineWidth: '4px',
            color: 'rgba(0, 0, 0, .2)',
            zeroLineColor: 'transparent'
          },
          ticks: $.extend({
            beginAtZero: true,

            // Include a dollar sign in the ticks
            callback: function (value) {
                value += ' Hp'

              return '' + value
            }
          }, ticksStyle)
        }],
        xAxes: [{
          display: true,
          gridLines: {
            display: false
          },
          ticks: ticksStyle
        }]
      }
    }
  })
})
// lgtm [js/unused-local-variable]
