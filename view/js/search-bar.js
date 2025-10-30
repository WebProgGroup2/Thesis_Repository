document.addEventListener("DOMContentLoaded", () => {
  const currentPath = window.location.pathname.toLowerCase(); // normalize case

  // Auto-detect correct redirect target
  let searchURL;
  if (currentPath.includes("archive.php")) {
    searchURL = `${window.location.origin}/thesis_repo/view/pages/archive.php`;
  } else {
    searchURL = `${window.location.origin}/thesis_repo/view/pages/search-result.php`;
  }

  /* ============================================================
     ============================================================ */
  const header = document.getElementById("header");
  if (!header) {
    console.warn("Header element not found.");
    return;
  }

  const searchWrapper = document.createElement("div");
  searchWrapper.classList.add("search-wrapper");
  searchWrapper.innerHTML = `
  <form id="search-form">
    <input type="text" id="search-bar" placeholder="Search..." />
    <button type="submit" id="search-btn">Search</button>
  </form>
  <span class="material-symbols-outlined" id="filter-btn">filter_alt</span>
  <div class="filters-wrapper" style="display: none;">
    <span class="filters active" data-filter="all">ALL</span>
    <span class="filters" data-filter="title">TITLE</span>
    <span class="filters" data-filter="author">AUTHOR</span>
    <span class="filters" data-filter="keyword">KEYWORD</span>
    <span class="filters" data-filter="advance">ADVANCE</span>
  </div>
`;

  header.appendChild(searchWrapper);

  /* ============================================================
     ============================================================ */
  const searchForm = document.getElementById("search-form");
  const searchBar = document.getElementById("search-bar");
  const filterOptions = document.querySelectorAll(".filters");
  let selectedFilter = "all";

  filterOptions.forEach((filter) => {
    filter.addEventListener("click", () => {
      filterOptions.forEach((f) => f.classList.remove("active"));
      filter.classList.add("active");
      selectedFilter = filter.getAttribute("data-filter");
    });
  });

  if (searchForm && searchBar) {
    searchForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const query = searchBar.value.trim();
      if (!query){
        window.location.href = searchURL;
      }

      const params = new URLSearchParams();
      params.set("q", query);
      if (selectedFilter) params.set("filter", selectedFilter);

      console.log("Redirecting to:", `${searchURL}?${params.toString()}`); // 🪶 Debug
      window.location.href = `${searchURL}?${params.toString()}`;
    });
  }

  /* ============================================================
     ============================================================ */
  const filterBtn = document.getElementById("filter-btn");
  const filtersWrapper = document.querySelector(".filters-wrapper");
  let visibleFilters = false;

  if (filterBtn && filtersWrapper) {
    filterBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      visibleFilters = !visibleFilters;
      filtersWrapper.style.display = visibleFilters ? "flex" : "none";
    });

    document.addEventListener("click", (e) => {
      if (
        visibleFilters &&
        !filtersWrapper.contains(e.target) &&
        e.target !== filterBtn
      ) {
        filtersWrapper.style.display = "none";
        visibleFilters = false;
      }
    });
  }
});
