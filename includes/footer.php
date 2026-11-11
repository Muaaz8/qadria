</div></div></div>
<script>
document.addEventListener('keydown', function(e) {
    if(e.ctrlKey && e.key === 's') { e.preventDefault(); let btn = document.querySelector('.btn-primary[type="submit"]'); if(btn) btn.click(); }
    if(e.ctrlKey && e.key === 'p') { e.preventDefault(); window.print(); }
    if(e.ctrlKey && e.key === 'n') { e.preventDefault(); window.location.href = 'system.php?page=create-job'; }
    if(e.ctrlKey && e.key === 'b') { e.preventDefault(); window.location.href = 'system.php?page=bills'; }
    if(e.ctrlKey && e.key === 'j') { e.preventDefault(); window.location.href = 'system.php?page=jobs'; }
    if(e.ctrlKey && e.key === 'd') { e.preventDefault(); window.location.href = 'system.php?page=dashboard'; }
});
</script>
</body>
</html>