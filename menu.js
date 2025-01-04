document.addEventListener("DOMContentLoaded", () => {
    const hamburgerButton = document.querySelector('.hamburger-menu');
    const mobileDropdownMenu = document.querySelector('.mobile-dropdown-menu');

    hamburgerButton.addEventListener('click', () => {
        console.log("Hamburger menu clicked"); // Add this
        mobileDropdownMenu.classList.toggle('visible');
        hamburgerButton.classList.toggle('active');
    });
});
