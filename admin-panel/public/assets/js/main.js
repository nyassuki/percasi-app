document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const sidebarCollapseBtn = document.getElementById('sidebarCollapse');
    const content = document.getElementById('content');

    // Guard WAJIB
    if (!sidebar || !content) {
        console.error('Sidebar or content element not found');
        return;
    }

    // Status dari localStorage
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        content.classList.add('expanded');
    }

    // Toggle sidebar (cek tombol dulu)
    if (sidebarCollapseBtn) {
        sidebarCollapseBtn.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');

            localStorage.setItem(
                'sidebarCollapsed',
                sidebar.classList.contains('collapsed')
            );

            window.dispatchEvent(new Event('sidebarToggle'));
        });
    }

    // Responsive auto collapse
    window.addEventListener('resize', function () {
        if (window.innerWidth < 768) {
            sidebar.classList.add('collapsed');
            content.classList.add('expanded');
            localStorage.setItem('sidebarCollapsed', 'true');
        }
    });
});

// Select2 (AMAN)
$(document).ready(function () {
    if ($('.select2').length) {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    // DataTable (FIX TYPO)
    if ($('.pagingTable').length) {
        $('.pagingTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Data tidak tersedia",
                zeroRecords: "Data tidak ditemukan",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "›",
                    previous: "‹"
                }
            }
        });
    }
});
