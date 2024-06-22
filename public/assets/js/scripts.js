const _document = $(document);
const modal = $(".modal");
const main = $("main");
const menuUser = $(".menu-user");

_document.ready(function () {});
_document.ajaxStart(() => $("#loading").fadeIn());
_document.ajaxStop(() => $("#loading").fadeOut());

_document.on("click", "#btn-cart", function () {
    window.location.href = "/gio-hang";
});

_document.on("click", ".box-avatar-nav", function () {
    menuUser.toggleClass("show");
});
_document.on("click", function (event) {
    if (
        !$(event.target).closest(".menu-user").length &&
        !$(event.target).closest(".box-avatar-nav").length
    ) {
        menuUser.removeClass("show");
    }
});

function Toast({ message = "", type = "info", duration = 5000 }) {
    const notifications = document.querySelector(".notifications");
    if (notifications) {
        let newToast = document.createElement("div");
        const icons = {
            success: "fas fa-check-circle",
            info: "fas fa-exclamation-circle",
            warning: "fas fa-exclamation-triangle",
            error: "fas fa-times-circle",
        };
        const icon = icons[type];
        const delay = (duration / 1000).toFixed(2);
        newToast.style.animation = `show 0.5s ease 1 forwards, hide 0.5s ease 1 forwards ${delay}s`;

        newToast.innerHTML = `
        <div class="toast ${type} show">
        <i class="${icon}"></i>
        <div class="content">
        <span>${message}</span>
        </div>
        <i class="fas fa-times" onclick="(this.parentElement).remove()"></i>
        </div>`;
        notifications.appendChild(newToast);
        newToast.timeOut = setTimeout(() => newToast.remove(), duration + 500);
    }
}

function openModal({
    title = "",
    body = "",
    ok = "",
    cancel = "",
    size = "modal-lg",
}) {
    modal.find(".modal-title").text(title);
    modal.find(".modal-body").empty().append(body);
    if (ok === "") {
        modal.find(".btn-primary").remove();
    } else {
        modal.find(".btn-primary").text(ok);
    }
    if (cancel === "") {
        modal.find(".btn-secondary").remove();
    } else {
        modal.find(".btn-secondary").text(cancel);
    }
    modal.find(".modal-dialog").addClass(size);
    modal.modal("show");
}
