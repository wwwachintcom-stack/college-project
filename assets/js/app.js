// Sidebar toggle
function toggleSidebar() {
    document.getElementById('sidebar')?.classList.toggle('open');
}
document.addEventListener('click', e => {
    const sb = document.getElementById('sidebar');
    if (!sb) return;
    if (window.innerWidth <= 768 && !sb.contains(e.target) && !e.target.closest('[onclick="toggleSidebar()"]')) {
        sb.classList.remove('open');
    }
});

// Auto-dismiss alerts
document.querySelectorAll('.alert').forEach(el => {
    setTimeout(() => { el.style.transition = 'opacity .5s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 500); }, 5000);
});

// Animate cards on load
const observer = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.style.opacity='1'; e.target.style.transform='translateY(0)'; } });
}, { threshold: 0.05 });

document.querySelectorAll('.stat-card, .card').forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(14px)';
    el.style.transition = `opacity .35s ease ${i * 0.04}s, transform .35s ease ${i * 0.04}s`;
    observer.observe(el);
});
