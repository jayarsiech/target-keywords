document.addEventListener("DOMContentLoaded", function () {
    // Tagify
    var input = document.querySelector("#tk_keywords_input");
    if (input) {
        new Tagify(input, {
            dropdown: { enabled: 0 }
        });
    }

    // Copy permalink on title click
    document.querySelectorAll(".tk-copy-related").forEach(function (el) {
        el.addEventListener("click", function () {
            const url = el.getAttribute("data-link");
            navigator.clipboard.writeText(url).then(function () {
                // پیام موفقیت
                const msg = document.getElementById("tk-copy-feedback");
                if (msg) {
                    msg.style.display = "inline";
                    setTimeout(() => {
                        msg.style.display = "none";
                    }, 1500);
                }
            });
        });
    });
});

// بررسی تکراری بودن کلیدواژه‌ها
function checkDuplicateKeywords(tagify) {
    const existing = (window.tk_existing_keywords || []).map(x => x.toLowerCase());
    const current = tagify.value.map(item => item.value.toLowerCase());

    const duplicates = current.filter(val => existing.includes(val));
    const warningBox = document.getElementById("tk-duplicate-warning");

    if (warningBox) {
        if (duplicates.length > 0) {
            warningBox.innerHTML = "⚠️ این کلمات در پست‌های دیگر هم استفاده شده‌اند: " + duplicates.join(', ');
            warningBox.style.display = "block";
        } else {
            warningBox.style.display = "none";
        }
    }
}

// اتصال به Tagify
document.addEventListener("DOMContentLoaded", function () {
    var input = document.querySelector("#tk_keywords_input");
    if (input) {
        var tagify = new Tagify(input, {
            dropdown: { enabled: 0 }
        });

        tagify.on('change', function() {
            checkDuplicateKeywords(tagify);
        });

        // بررسی اولیه
        checkDuplicateKeywords(tagify);
    }

    // کپی از پست‌های مرتبط
    document.querySelectorAll(".tk-copy-related").forEach(function (el) {
        el.addEventListener("click", function () {
            const url = el.getAttribute("data-link");
            navigator.clipboard.writeText(url).then(function () {
                const msg = document.getElementById("tk-copy-feedback");
                if (msg) {
                    msg.style.display = "inline";
                    setTimeout(() => {
                        msg.style.display = "none";
                    }, 1500);
                }
            });
        });
    });
});
