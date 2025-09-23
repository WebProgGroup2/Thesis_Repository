 // Star Rating JS
    const stars = document.querySelectorAll(".thesis__stars span");
    stars.forEach(star => {
      star.addEventListener("click", () => {
        stars.forEach(s => s.classList.remove("active"));
        star.classList.add("active");
        let rating = star.dataset.value;
        console.log("Rated:", rating);
      });
    });