document.addEventListener("DOMContentLoaded", () => {
    const navbar = document.querySelector(".jg-navbar");

    window.addEventListener(
        "scroll",
        () => {
            navbar?.classList.toggle("scrolled", window.scrollY > 12);
        },
        { passive: true },
    );
});
