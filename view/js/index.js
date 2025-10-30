/* ============================================================
     ============================================================ */
document.addEventListener("DOMContentLoaded", () => {
  const carousel = document.getElementById("event-carousel");
  const speed = 1;
  const interval = 16;
  let scrollInterval;
  

  const images = [
    "view/assets/carousel-imgs/carousel-img1.jpg",
    "view/assets/carousel-imgs/carousel-img2.jpg",
    "view/assets/carousel-imgs/carousel-img3.jpg",
    "view/assets/carousel-imgs/carousel-img4.jpg",
  ];


  function populateCarousel(imgList) {
    carousel.innerHTML = "";
    imgList.forEach((src, index) => {
      const img = document.createElement("img");
      img.src = src;
      img.alt = `Slide ${index + 1}`;
      img.classList.add("carousel-img");
      carousel.appendChild(img);
    });

    carousel.innerHTML += carousel.innerHTML;
  }

  function autoScroll() {
    carousel.scrollLeft += speed;
    if (carousel.scrollLeft >= carousel.scrollWidth / 2) {
      carousel.scrollLeft = 0; 
    }
  }

  function startScroll() {
    scrollInterval = setInterval(autoScroll, interval);
  }

  function stopScroll() {
    clearInterval(scrollInterval);
  }


  if (carousel) {
    populateCarousel(images);
    startScroll();

    carousel.addEventListener("mouseenter", stopScroll);
    carousel.addEventListener("mouseleave", startScroll);
  }

  
});
