// Lostly front-end behaviour. Plain JavaScript on top of Bootstrap 5
// (menus, dropdowns, pop-ups, toasts and collapses come from Bootstrap).

document.addEventListener("DOMContentLoaded", function () {

    var hasBootstrap = typeof bootstrap !== "undefined";
    var root = document.documentElement;
    var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    function store(key, value) {
        try { localStorage.setItem(key, value); } catch (err) {}
    }

    // ---- light / dark mode ----
    document.querySelectorAll(".js-theme-toggle").forEach(function (button) {
        button.addEventListener("click", function () {
            var next = root.getAttribute("data-bs-theme") === "dark" ? "light" : "dark";
            root.setAttribute("data-bs-theme", next);
            store("lostly-theme", next);
        });
    });

    // ---- colour scheme (palette button) ----
    function markPalette() {
        var current = root.getAttribute("data-palette") || "stone";
        document.querySelectorAll(".js-palette").forEach(function (item) {
            var on = item.getAttribute("data-set-palette") === current;
            item.classList.toggle("active", on);
            item.setAttribute("aria-pressed", on ? "true" : "false");
        });
    }

    document.querySelectorAll(".js-palette").forEach(function (item) {
        item.addEventListener("click", function () {
            var id = item.getAttribute("data-set-palette");
            root.setAttribute("data-palette", id);
            store("lostly-palette", id);
            markPalette();
        });
    });

    markPalette();

    // ---- one-time messages as Bootstrap toasts ----
    document.querySelectorAll(".lostly-toast").forEach(function (el) {
        if (hasBootstrap) { bootstrap.Toast.getOrCreateInstance(el, { delay: 5000 }).show(); }
        else { el.classList.add("show"); }
    });

    // ---- "are you sure?" before forms marked with data-confirm ----
    var modalEl = document.getElementById("confirmModal");
    var pendingForm = null;
    var modal = hasBootstrap && modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;

    if (modal) {
        modalEl.querySelector(".js-confirm-yes").addEventListener("click", function () {
            if (pendingForm) {
                var form = pendingForm;
                pendingForm = null;
                form.setAttribute("data-confirmed", "1");
                modal.hide();
                if (form.requestSubmit) { form.requestSubmit(); } else { form.submit(); }
            }
        });
        modalEl.addEventListener("hidden.bs.modal", function () { pendingForm = null; });
    }

    document.querySelectorAll("form[data-confirm]").forEach(function (form) {
        form.addEventListener("submit", function (e) {
            if (form.getAttribute("data-confirmed")) { return; }
            var message = form.getAttribute("data-confirm") || "Are you sure?";

            if (modal) {
                e.preventDefault();
                pendingForm = form;
                modalEl.querySelector(".js-confirm-text").textContent = message;
                modal.show();
            } else if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // ---- form checks before sending ----
    // Fields describe their own rules:
    //   data-required="message"   data-minlength="6" data-minlength-message="..."
    //   data-match="other field"  data-match-message="..."
    // PHP checks everything again, so this is only for a faster answer.
    function showFormError(form, message, field) {
        var old = form.querySelector(".js-form-message");
        if (old) { old.remove(); }

        var box = document.createElement("div");
        box.className = "alert alert-lost form-message js-form-message mt-3";
        box.setAttribute("role", "alert");
        box.textContent = message;

        var submit = form.querySelector("[type=submit]");
        var anchor = submit ? (submit.closest(".form-actions") || submit) : null;

        if (anchor && anchor.parentNode && !form.classList.contains("chat-form") && !form.classList.contains("tip-form")) {
            anchor.parentNode.insertBefore(box, anchor);
        } else {
            form.appendChild(box);
        }

        if (field) { field.focus(); }
    }

    document.querySelectorAll("form.js-validate").forEach(function (form) {
        form.addEventListener("submit", function (e) {
            var fields = form.querySelectorAll("input, select, textarea");

            for (var i = 0; i < fields.length; i++) {
                var field = fields[i];
                var value = field.type === "password" ? field.value : field.value.trim();
                var min = parseInt(field.getAttribute("data-minlength"), 10);
                var matchName = field.getAttribute("data-match");
                var error = "";

                if (field.hasAttribute("data-required") && value === "") {
                    error = field.getAttribute("data-required");
                } else if (min && value !== "" && value.length < min) {
                    error = field.getAttribute("data-minlength-message") || "Too short";
                } else if (matchName && form.elements[matchName] && form.elements[matchName].value !== field.value) {
                    error = field.getAttribute("data-match-message") || "Fields do not match";
                }

                if (error) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    showFormError(form, error, field);
                    return;
                }
            }
        });
    });

    // Stop double submits (a second click while the page is loading).
    document.querySelectorAll("form[method=post]").forEach(function (form) {
        form.addEventListener("submit", function (e) {
            if (e.defaultPrevented) { return; }
            if (form.getAttribute("data-sending")) { e.preventDefault(); return; }
            form.setAttribute("data-sending", "1");
            setTimeout(function () { form.removeAttribute("data-sending"); }, 4000);
        });
    });

    // ---- report form ----
    document.querySelectorAll(".js-photos-input").forEach(function (input) {
        input.addEventListener("change", function () {
            var box = document.getElementById(input.getAttribute("data-preview"));
            var max = parseInt(input.getAttribute("data-max"), 10) || 4;
            var files = input.files ? Array.prototype.slice.call(input.files) : [];

            if (box) { box.innerHTML = ""; }

            if (files.length > max) {
                alert("You can add up to " + max + " photos.");
                input.value = "";
                return;
            }

            for (var i = 0; i < files.length; i++) {
                if (files[i].size > 5 * 1024 * 1024) {
                    alert("Each image must be smaller than 5MB");
                    input.value = "";
                    if (box) { box.innerHTML = ""; }
                    return;
                }
                if (box) {
                    var img = document.createElement("img");
                    img.src = URL.createObjectURL(files[i]);
                    img.alt = "Photo preview " + (i + 1);
                    box.appendChild(img);
                }
            }
        });
    });

    // The reward field only applies to Lost reports.
    var rewardField = document.querySelector(".js-reward-field");
    if (rewardField) {
        document.querySelectorAll('input[name="type"]').forEach(function (radio) {
            radio.addEventListener("change", function () {
                rewardField.hidden = !(radio.checked && radio.value === "Lost");
            });
        });
    }

    // District suggestions follow the chosen division.
    document.querySelectorAll("datalist[data-districts]").forEach(function (list) {
        var form = list.closest("form");
        var division = form ? form.querySelector('select[name="division"]') : null;
        var districts = {};

        try { districts = JSON.parse(list.getAttribute("data-districts")) || {}; } catch (err) {}

        function fill() {
            var names = division && districts[division.value] ? districts[division.value] : [];
            if (names.length === 0) {
                Object.keys(districts).forEach(function (key) { names = names.concat(districts[key]); });
            }
            list.innerHTML = "";
            names.forEach(function (name) {
                var option = document.createElement("option");
                option.value = name;
                list.appendChild(option);
            });
        }

        fill();
        if (division) { division.addEventListener("change", fill); }
    });

    // ---- report page ----
    var galleryMain = document.querySelector(".js-gallery-main");
    document.querySelectorAll(".js-gallery-thumb").forEach(function (thumb) {
        thumb.addEventListener("click", function () {
            if (!galleryMain) { return; }
            galleryMain.src = thumb.querySelector("img").src;
            document.querySelectorAll(".js-gallery-thumb").forEach(function (other) {
                other.classList.toggle("active", other === thumb);
            });
        });
    });

    document.querySelectorAll(".js-copy-link").forEach(function (button) {
        button.addEventListener("click", function () {
            var link = button.getAttribute("data-link");
            var label = button.innerHTML;
            function done() {
                button.textContent = "Copied ✓";
                setTimeout(function () { button.innerHTML = label; }, 1800);
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(link).then(done, function () { prompt("Copy this link:", link); });
            } else {
                prompt("Copy this link:", link);
            }
        });
    });

    // ---- browse filters apply as soon as they change ----
    document.querySelectorAll(".js-autosubmit").forEach(function (select) {
        select.addEventListener("change", function () { select.form.submit(); });
    });

    // ---- demo accounts on the login page ----
    document.querySelectorAll(".js-demo-login").forEach(function (button) {
        button.addEventListener("click", function () {
            var form = document.getElementById("login-form");
            form.elements.username.value = button.getAttribute("data-user");
            form.elements.password.value = button.getAttribute("data-pass");
            if (form.requestSubmit) { form.requestSubmit(); } else { form.submit(); }
        });
    });

    // ---- chat: newest at the bottom, Enter sends, live updates ----
    var chat = document.querySelector(".js-chat");

    if (chat) {
        var lastId = parseInt(chat.getAttribute("data-last"), 10) || 0;
        var url = chat.getAttribute("data-url");
        chat.scrollTop = chat.scrollHeight;

        var poll = function () {
            if (document.hidden) { return; }

            fetch(url + "&after=" + lastId, { credentials: "same-origin" })
                .then(function (response) { return response.ok ? response.json() : []; })
                .then(function (messages) {
                    messages.forEach(function (m) {
                        if (m.id <= lastId) { return; }
                        lastId = m.id;

                        var empty = chat.querySelector(".js-chat-empty");
                        if (empty) { empty.remove(); }

                        var bubble = document.createElement("div");
                        bubble.className = "bubble " + (m.mine ? "mine" : "theirs");
                        var p = document.createElement("p");
                        p.textContent = m.body;            // plain text: safe from HTML
                        p.style.whiteSpace = "pre-wrap";
                        var time = document.createElement("time");
                        time.textContent = m.time;
                        bubble.appendChild(p);
                        bubble.appendChild(time);
                        chat.appendChild(bubble);
                        chat.scrollTop = chat.scrollHeight;
                    });
                })
                .catch(function () {});
        };

        setInterval(poll, 4000);
    }

    document.querySelectorAll(".js-enter-submit").forEach(function (area) {
        area.addEventListener("keydown", function (e) {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                if (area.form.requestSubmit) { area.form.requestSubmit(); } else { area.form.submit(); }
            }
        });
    });

    // ---- animations: cards and panels rise in as they scroll into view ----
    function countUp(el) {
        var target = parseInt(el.getAttribute("data-count"), 10) || 0;
        if (reduceMotion || target <= 0 || el.getAttribute("data-counted")) { return; }
        el.setAttribute("data-counted", "1");

        var start = null;
        function step(now) {
            if (start === null) { start = now; }
            var progress = Math.min(1, (now - start) / 900);
            el.textContent = Math.round(target * (1 - Math.pow(1 - progress, 3)));
            if (progress < 1) { requestAnimationFrame(step); } else { el.textContent = target; }
        }
        el.textContent = "0";
        requestAnimationFrame(step);
    }

    function show(el, delay) {
        el.style.setProperty("--delay", delay + "ms");
        el.classList.add("in");
        el.querySelectorAll(".js-count").forEach(countUp);
    }

    document.querySelectorAll(".panel, .stat-tile, .item-card, .how-step, .auth-box, .auth-side, .page-hero, .hero-visual, .ticker, .stats-band, .empty, .section-title, .pagination, .category-tile, .reunited-card, .cta-band, .accordion, .division-chip")
        .forEach(function (el) {
            if (!el.parentElement.closest(".reveal") && !el.closest(".modal")) { el.classList.add("reveal"); }
        });

    var revealed = document.querySelectorAll(".reveal");

    if (reduceMotion || !("IntersectionObserver" in window)) {
        revealed.forEach(function (el) { show(el, 0); });
    } else {
        var observer = new IntersectionObserver(function (entries) {
            var order = 0;
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    show(entry.target, Math.min(order, 8) * 80);
                    order++;
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0, rootMargin: "0px 0px -30px 0px" });

        revealed.forEach(function (el) { observer.observe(el); });
    }

    document.querySelectorAll(".js-count").forEach(function (el) {
        if (!el.closest(".reveal")) { countUp(el); }
    });
});
