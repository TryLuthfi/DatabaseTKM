/* global Chart:false */

$(function () {
  'use strict'

  var ticksStyle = {
    fontColor: '#495057',
    fontStyle: 'bold'
  }

  var mode = 'index'
  var intersect = true

  var $fiberstarChartBar = $('#fiberstar_chart_bar')
  // eslint-disable-next-line no-unused-vars
  var fiberstarChartBar = new Chart($fiberstarChartBar, {
    type: 'bar',
    data: {
      labels: ['TEGAL', 'KEDIRI', 'PALEMBANG', 'JAKARTA', 'BANDUNG', 'LAMPUNG', 'BANJARMASIN', 'PEKANBARU'],
      datasets: [
        {
          backgroundColor: '#007bff',
          borderColor: '#007bff',
          data: [4880, 4600, 3082, 2560, 2300, 1860, 898, 538]
        },
        {
          backgroundColor: '#ced4da',
          borderColor: '#ced4da',
          data: [3000, 4500, 2700, 2000, 1800, 1500, 2000, 1000]
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

  var $fiberstarChartLine = $('#fiberstar_chart_line')
  // eslint-disable-next-line no-unused-vars
  var fiberstarChartLine = new Chart($fiberstarChartLine, {
    data: {
      labels: ['TEGAL', 'KEDIRI', 'PALEMBANG', 'JAKARTA', 'BANDUNG', 'LAMPUNG', 'BANJARMASIN', 'PEKANBARU'],
      datasets: [{
        type: 'line',
        data: [4880, 4600, 3082, 2560, 2300, 1860, 898, 538],
        backgroundColor: 'transparent',
        borderColor: '#007bff',
        pointBorderColor: '#007bff',
        pointBackgroundColor: '#007bff',
        fill: false
        // pointHoverBackgroundColor: '#007bff',
        // pointHoverBorderColor    : '#007bff'
      },
      {
        type: 'line',
        data: [3000, 4500, 2700, 2000, 1800, 1500, 2000, 1000],
        backgroundColor: 'tansparent',
        borderColor: '#ced4da',
        pointBorderColor: '#ced4da',
        pointBackgroundColor: '#ced4da',
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
