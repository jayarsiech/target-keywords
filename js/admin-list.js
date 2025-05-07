document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("tk-search");
    if (searchInput) {
        searchInput.addEventListener("keyup", function () {
            var search = this.value.toLowerCase();
            document.querySelectorAll("#tk-table tbody tr").forEach(function (row) {
                row.style.display = row.innerText.toLowerCase().includes(search) ? "" : "none";
            });
        });
    }

    document.querySelectorAll(".tk-copy-button").forEach(function (btn) {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            const link = btn.getAttribute("data-link");
            navigator.clipboard.writeText(link).then(function () {
                btn.textContent = "âœ…";
                setTimeout(() => btn.textContent = "ðŸ“‹", 1000);
            });
        });
    });
});
