const modal = new mdb.Modal($(".modal"));
const _document = $(document);
const row = $(".row");

_document.ready(function () {});
_document.ajaxStart(() => $("#loading").fadeIn());
_document.ajaxStop(() => $("#loading").fadeOut());

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
