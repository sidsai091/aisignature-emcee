/* =============================================
   ADMIN DASHBOARD — JAVASCRIPT
   ============================================= */

/* ---- Sidebar toggle (mobile) ---- */
(function () {
  const toggle  = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  if (!toggle || !sidebar) return;

  toggle.addEventListener('click', function () {
    sidebar.classList.toggle('open');
  });

  // Close when clicking outside on mobile
  document.addEventListener('click', function (e) {
    if (sidebar.classList.contains('open') &&
        !sidebar.contains(e.target) &&
        e.target !== toggle) {
      sidebar.classList.remove('open');
    }
  });
})();

/* ---- Revenue chart initialiser ---- */
function initRevenueChart(canvasId, labels, values) {
  const canvas = document.getElementById(canvasId);
  if (!canvas || typeof Chart === 'undefined') return;

  const gold    = '#b8860b';
  const goldBg  = 'rgba(184,134,11,0.1)';
  const text    = '#2d2d2d';
  const muted   = '#7a7060';
  const border  = 'rgba(184,134,11,0.15)';

  new Chart(canvas, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Revenue (RM)',
        data: values,
        backgroundColor: goldBg,
        borderColor: gold,
        borderWidth: 1.5,
        borderRadius: 5,
        borderSkipped: false,
        hoverBackgroundColor: 'rgba(184,134,11,0.2)',
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#ffffff',
          borderColor: border,
          borderWidth: 1,
          titleColor: gold,
          bodyColor: text,
          padding: 10,
          callbacks: {
            label: function(ctx) {
              return ' RM' + ctx.parsed.y.toLocaleString();
            }
          }
        }
      },
      scales: {
        x: {
          grid: { color: 'rgba(0,0,0,0.03)' },
          ticks: { color: muted, font: { size: 11 } }
        },
        y: {
          grid: { color: 'rgba(0,0,0,0.04)' },
          ticks: {
            color: muted,
            font: { size: 11 },
            callback: function(v) { return 'RM' + v.toLocaleString(); }
          },
          beginAtZero: true
        }
      }
    }
  });
}
