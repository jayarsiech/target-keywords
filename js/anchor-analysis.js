document.addEventListener("DOMContentLoaded", function () {
    const rowsPerPage = 10;
    const allRows = Array.from(document.querySelectorAll("#tk-anchor-table tbody tr"));
    let filteredRows = [...allRows];
    let currentPage = 1;

    const searchInput = document.getElementById("tk-search-anchor");

function paginate(rowsToPaginate, page = 1) {
    currentPage = page;
    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;

    // پنهان کردن همه
    allRows.forEach(row => row.style.display = "none");

    // فقط نمایش ردیف‌های صفحه فعلی از rowsToPaginate
    rowsToPaginate.slice(start, end).forEach(row => {
        row.style.display = "";
    });

    renderPagination(rowsToPaginate, page);
}


function renderPagination(rows, currentPage) {
    const container = document.getElementById("tk-pagination");
    container.innerHTML = "";

    const pageCount = Math.ceil(rows.length / rowsPerPage);
    if (pageCount <= 1) return;

    const createBtn = (text, page, isActive = false, disabled = false) => {
        const btn = document.createElement("button");
        btn.textContent = text;
        btn.className = "button" + (isActive ? " primary" : "");
        btn.style.margin = "0 3px";
        btn.disabled = disabled;
        btn.addEventListener("click", () => {
            paginate(rows, page);
        });
        return btn;
    };

    container.appendChild(createBtn("◀ قبل", currentPage - 1, false, currentPage === 1));

    const range = 2;
    const minPage = Math.max(1, currentPage - range);
    const maxPage = Math.min(pageCount, currentPage + range);

    for (let i = minPage; i <= maxPage; i++) {
        container.appendChild(createBtn(i, i, i === currentPage));
    }

    container.appendChild(createBtn("بعد ▶", currentPage + 1, false, currentPage === pageCount));
}
paginate(allRows, 1);


if (searchInput) {
    searchInput.addEventListener("keyup", function () {
        const input = this.value.toLowerCase().trim();

        if (!input) {
            filteredRows = [...allRows];
            paginate(filteredRows, 1);
            return;
        }

        const searchWords = input.split(/\s+/).filter(Boolean);

        filteredRows = allRows.filter(row => {
            const anchorEl = row.querySelector("td strong");
            if (!anchorEl) return false;

            const anchorText = anchorEl.textContent.toLowerCase().trim();

            // فقط انکری که همه کلمات رو داشته باشه نگه دار
            return searchWords.every(word => anchorText.includes(word));
        });

        paginate(filteredRows, 1);
    });
}



    // نمایش اولیه
    paginate(filteredRows, currentPage);

    // مدیریت مدال‌ها
    document.body.addEventListener("click", function (e) {
        if (e.target.classList.contains("tk-show-details")) {
            e.preventDefault();
            const key = e.target.getAttribute("data-target");
            const modal = document.getElementById("tk-modal-" + key);
            if (modal) {
                modal.querySelectorAll("tbody tr").forEach(row => row.style.display = "");
                modal.style.display = "flex";
            }
        }

        if (e.target.classList.contains("tk-close-modal")) {
            e.preventDefault();
            e.target.closest(".tk-anchor-modal").style.display = "none";
        }
    });
    
    
let sortAsc = true;

document.getElementById("tk-sort-usage").addEventListener("click", function () {
    allRows.forEach(row => row.style.display = "none"); // مخفی‌سازی همه

// سورت آرایه جاوااسکریپت
filteredRows.sort((a, b) => {
    const aCount = parseInt(a.getAttribute("data-usage")) || 0;
    const bCount = parseInt(b.getAttribute("data-usage")) || 0;
    return sortAsc ? aCount - bCount : bCount - aCount;
});

// بازنویسی DOM طبق ترتیب جدید
const tbody = document.querySelector("#tk-anchor-table tbody");
filteredRows.forEach(row => {
    tbody.appendChild(row); // جابجا کردن tr به ترتیب جدید
});

// سورت بعدی معکوس بشه
sortAsc = !sortAsc;

// دوباره صفحه‌بندی کن
paginate(filteredRows, 1);
 // فقط ردیف‌های سورت‌شده فیلتر شده نمایش داده شوند
});

   
    
});
