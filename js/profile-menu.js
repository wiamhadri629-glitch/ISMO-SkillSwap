const profileToggle = document.getElementById("profileToggle");
const profileMenu = document.getElementById("profileMenu");
const chevronIcon = document.getElementById("chevronIcon");
if (profileToggle) {
    profileToggle.addEventListener("click", (e) => {
        e.stopPropagation();
        if (profileMenu) { profileMenu.classList.toggle("open"); }
        if (chevronIcon) { chevronIcon.classList.toggle("rotated"); }
    });
    document.addEventListener("click", (e) => {
        if (profileToggle && profileMenu && chevronIcon) {
            if (!profileToggle.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.classList.remove("open");
                chevronIcon.classList.remove("rotated");
            }
        }
    });
}
