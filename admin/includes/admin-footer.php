            </div>
        </div>
    </div>
    <script src="/assets/js/app.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var toggle = document.getElementById('sidebarToggle');
        var sidebar = document.getElementById('adminSidebar');
        if (toggle && sidebar) {
            toggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
            });
        }
    });
    </script>
</body>
</html>
