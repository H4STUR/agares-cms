(function () {
  const timeline = window.GA_TRAFFIC_TIMELINE;
  const el = document.querySelector('#gaTrafficChart');
  if (!el) return;

  if (!timeline || !timeline.ok) {
    el.innerHTML = `
      <div class="d-flex align-items-center justify-content-center h-100">
        <div class="alert alert-warning mb-0 text-center" style="max-width:520px;">
          <div class="fw-semibold mb-1">Google Analytics not ready</div>
          <div class="small">${timeline?.error ?? 'Timeline not available'}</div>
        </div>
      </div>`;
    return;
  }

  if (typeof ApexCharts === 'undefined') {
    el.innerHTML = `<div class="alert alert-danger mb-0">ApexCharts not loaded.</div>`;
    return;
  }

  const options = {
    series: timeline.series,
    chart: {
      type: 'bar',
      height: 320,
      toolbar: { show: false },
      foreColor: '#9ba7b2'
    },
    plotOptions: {
      bar: {
        horizontal: false,
        borderRadius: 4,
        columnWidth: '55%',
        endingShape: 'rounded'
      }
    },
    dataLabels: { enabled: false },
    stroke: { width: 4, colors: ['transparent'] },
    xaxis: {
      categories: timeline.labels,
      tickPlacement: 'between',
    },
    yaxis: {
      labels: { formatter: (v) => Math.round(v).toString() }
    },
    tooltip: {
      theme: 'dark',
      shared: true,
      intersect: false
    },
    legend: {
      position: 'bottom',
      markers: { width: 8, height: 8, radius: 999 }
    },
    grid: {
      strokeDashArray: 4,
      borderColor: 'rgba(0,0,0,0.15)'
    }
  };

  if (window.__gaTrafficChart) {
    try { window.__gaTrafficChart.destroy(); } catch (e) {}
  }
  window.__gaTrafficChart = new ApexCharts(el, options);
  window.__gaTrafficChart.render();
})();
