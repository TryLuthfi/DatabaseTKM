/* global Chart:false */

$(function () {
  'use strict'

  var ticksStyle = {
    fontColor: '#495057',
    fontStyle: 'bold'
  }

  var mode = 'index'
  var intersect = true

  var $userChartBar = $('#user_chart_bar')
  // eslint-disable-next-line no-unused-vars
  var userChartBar = new Chart($userChartBar, {
    type: 'bar',
    data: {
      labels: ['Project Manager', 'Site Manager', 'Field Supervisor', 'Admin Project', 'Finance', 'Logistik'],
      datasets: [
        {
          backgroundColor: '#007bff',
          borderColor: '#007bff',
          data: [5, 5, 10, 5, 2, 2]
        },
        {
          backgroundColor: '#ced4da',
          borderColor: '#ced4da',
          data: [1, 0, 1, 0, 0, 0]
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
                value += ' Person'

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

  var $areaChatLine = $('#area_chart_line')
  // eslint-disable-next-line no-unused-vars
  var areaChatLine = new Chart($areaChatLine, {
    data: {
      labels: ['TEGAL', 'KEDIRI', 'PALEMBANG', 'JAKARTA', 'BANDUNG', 'LAMPUNG', 'BANJARMASIN', 'PEKANBARU'],
      datasets: [{
        type: 'line',
        data: [1860, 898, 538, 2300, 4600, 1860, 3082, 1098],
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
        data: [211, 3000, 1200, 2000, 1670, 1500, 1700, 1000],
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
