// Menampilkan pop-up saat halaman dimuat
window.onload = function () {
  document.getElementById("popup").style.display = "block";
};

// Mengelola slide
const slides = document.querySelectorAll(".slide");
let currentSlide = 0;

function showSlide(index) {
  slides.forEach((slide, i) => {
    slide.classList.remove("active");
    if (i === index) {
      slide.classList.add("active");
    }
  });
  // Tampilkan tombol close hanya pada slide terakhir
  document.getElementById("closePopup").style.display = index === slides.length - 1 ? "block" : "none";
}

// Menangani klik tombol "Berikutnya"
document.querySelectorAll(".next").forEach((button) => {
  button.addEventListener("click", () => {
    if (currentSlide < slides.length - 1) {
      currentSlide++;
      showSlide(currentSlide);
    }
  });
});

// Menangani klik tombol "Tutup" pada slide terakhir
document.querySelector(".finish").addEventListener("click", () => {
  document.getElementById("popup").style.display = "none";
});

// Tampilkan slide pertama saat pop-up dibuka
showSlide(currentSlide);
