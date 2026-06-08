/* ==========================================================
   products-mobile.js
   1. Filter toggle (icon bộ lọc)
   2. Pagination rút gọn trên mobile (1 2 3 ... 8 9 10)
========================================================== */

document.addEventListener('DOMContentLoaded', function () {

    /* ----------------------------------------------------------
       1. FILTER TOGGLE
       HTML cần có:
         <button class="btn-filter-toggle" id="btnFilterToggle">
           <span class="material-symbols-outlined filter-icon">tune</span>
           <span class="filter-label">Bộ lọc</span>
         </button>

         <div class="filter-panel" id="filterPanel">
           ... nội dung bộ lọc (danh mục, thương hiệu, giá...) ...
         </div>
    ---------------------------------------------------------- */
    const btnToggle = document.getElementById('btnFilterToggle');
    const filterPanel = document.getElementById('filterPanel');

    if (btnToggle && filterPanel) {
        btnToggle.addEventListener('click', function () {
            const isOpen = filterPanel.classList.toggle('open');
            btnToggle.classList.toggle('active', isOpen);

            // Đổi label khi open/close
            const label = btnToggle.querySelector('.filter-label');
            const icon  = btnToggle.querySelector('.filter-icon');
            if (label) label.textContent = isOpen ? 'Đóng' : 'Bộ lọc';
            if (icon)  icon.textContent  = isOpen ? 'close' : 'tune';
        });
    }

    /* ----------------------------------------------------------
       2. PAGINATION RÚT GỌN
       Chỉ chạy trên màn <= 600px.
       Logic: hiện prev | 1 2 3 ... (n-2) (n-1) n | next
       Nếu trang hiện tại ở giữa: prev | 1 ... (cur-1) cur (cur+1) ... n | next
    ---------------------------------------------------------- */
    function buildSmartPagination() {
        if (window.innerWidth > 600) return;

        const paginationEl = document.querySelector('.pagination-matrix .pagination');
        if (!paginationEl) return;

        const allItems = Array.from(paginationEl.querySelectorAll('.page-item'));
        // Lọc ra các item có số trang (bỏ prev/next/disabled)
        const pageItems = allItems.filter(item => {
            const link = item.querySelector('.page-link');
            if (!link) return false;
            const txt = link.textContent.trim();
            return /^\d+$/.test(txt);
        });

        if (pageItems.length <= 7) return; // Đủ ít, không cần rút gọn

        const total = pageItems.length;
        const activeItem = paginationEl.querySelector('.page-item.active');
        const activePage = activeItem
            ? parseInt(activeItem.querySelector('.page-link').textContent.trim())
            : 1;

        // Tập hợp index trang cần hiện (1-based)
        const show = new Set();
        // Luôn hiện 3 đầu và 3 cuối
        [1, 2, 3, total - 2, total - 1, total].forEach(p => {
            if (p >= 1 && p <= total) show.add(p);
        });
        // Hiện trang hiện tại ± 1
        [activePage - 1, activePage, activePage + 1].forEach(p => {
            if (p >= 1 && p <= total) show.add(p);
        });

        // Áp dụng: ẩn trang không nằm trong show, chèn "..."
        let prevVisible = true;
        pageItems.forEach((item, idx) => {
            const pageNum = idx + 1;
            if (show.has(pageNum)) {
                item.style.display = '';
                prevVisible = true;
            } else {
                if (prevVisible) {
                    // Chèn dấu "..." trước khi ẩn
                    const ellipsis = document.createElement('li');
                    ellipsis.className = 'page-item disabled pagination-ellipsis';
                    ellipsis.innerHTML = '<span class="page-link" style="background:transparent;border-color:transparent;color:#5e8a9c;">…</span>';
                    item.parentNode.insertBefore(ellipsis, item);
                }
                item.style.display = 'none';
                prevVisible = false;
            }
        });
    }

    buildSmartPagination();

    // Chạy lại nếu resize qua ngưỡng 600px
    let lastWidth = window.innerWidth;
    window.addEventListener('resize', function () {
        if (Math.abs(window.innerWidth - lastWidth) > 50) {
            lastWidth = window.innerWidth;
            // Xoá ellipsis cũ rồi build lại
            document.querySelectorAll('.pagination-ellipsis').forEach(el => el.remove());
            document.querySelectorAll('.pagination .page-item').forEach(el => el.style.display = '');
            buildSmartPagination();
        }
    });
});
