document.addEventListener("DOMContentLoaded", () => {
  // ============================================================
  // ============================================================
  const resultWrapper = document.getElementById("result-wrapper");
  const paginationWrapper = document.querySelector(
    ".pagination-wrapper .buttons-wrapper"
  );
  const BASE_URL = `${window.location.origin}/thesis_repo/`;
  const urlParams = new URLSearchParams(window.location.search);
  const query = urlParams.get("q") || "";
  const filter = urlParams.get("filter") || "all";
  const RESULTS_PER_PAGE = 10;

  let allResultsHTML = [];
  let currentPage = 1;

  // ============================================================
  // ============================================================
  async function fetchResults() {
    const selectedPrograms = getSelectedValues("PROGRAMS");
    const selectedMethods = getSelectedValues("METHODOLOGY");
    const selectedTypes = getSelectedValues("THESIS_TYPE");
    const filterMode =
      filter === "all" || filter === "advance" ? "none" : filter;

    const params = new URLSearchParams({
      ajax: 1,
      q: query,
      filter: filterMode,
      programs: selectedPrograms.join(","),
      methods: selectedMethods.join(","),
      types: selectedTypes.join(","),
    });

    resultWrapper.innerHTML = "<p>Loading results...</p>";

    try {
      const response = await fetch(
        `${BASE_URL}controller/search.php?${params}`
      );
      if (!response.ok) throw new Error("HTTP " + response.status);

      const json = await response.json();

      if (
        !json.success ||
        !Array.isArray(json.data) ||
        json.data.length === 0
      ) {
        resultWrapper.innerHTML = "<p>No results found.</p>";
        paginationWrapper.innerHTML = "";
        sessionStorage.removeItem("searchResults");
        return;
      }

   
      allResultsHTML = json.data.map((row, index) => {
        const div = document.createElement("div");
        div.classList.add("result-card");
        div.dataset.id = row.thesis_id;
        div.dataset.number = row.number;

        const firstAuthor = row.author ? row.author : "Unknown author";

        div.innerHTML = `
          <div class="result-number"><span class="number">${row.number}</span></div>
          <div class="content-wrapper">
            <h3>${escapeHTML(row.title || "Untitled")}</h3>
            <p class="author">by ${escapeHTML(firstAuthor)}</p>
            <p>${sanitizeAbstract(row.abstract) || "No abstract available."}</p>
            <small>Published: ${escapeHTML(row.pub_date || "Unknown")} | Views: ${
                  row.visit_count || 0
                }</small>
          </div>
        `;
        return div;
      });

      renderPage(1);

    
      const thesisOrder = allResultsHTML.map((div) => ({
        id: div.dataset.id,
        number: div.dataset.number,
      }));
      sessionStorage.setItem("searchResults", JSON.stringify(thesisOrder));
    } catch (err) {
      console.error("Error fetching results:", err);
      resultWrapper.innerHTML = "<p>Failed to load results.</p>";
    }
  }

  function sanitizeAbstract(html) {
    const temp = document.createElement("div");
    temp.innerHTML = html;

 
    const dangerous = temp.querySelectorAll(
      "script, style, link, meta, iframe, object, embed, form, input, button, textarea, select"
    );
    dangerous.forEach((el) => el.remove());


    const allElements = temp.querySelectorAll("*");
    allElements.forEach((el) => {
      Array.from(el.attributes).forEach((attr) => {
        if (
          attr.name.startsWith("on") || 
          ["src", "href", "action", "xmlns"].includes(attr.name.toLowerCase())
        ) {
          el.removeAttribute(attr.name);
        }
      });
    });

    return temp.innerHTML;
  }

  // ============================================================
  // ============================================================
  function renderPage(page) {
    currentPage = page;
    const start = (page - 1) * RESULTS_PER_PAGE;
    const end = start + RESULTS_PER_PAGE;
    const pageResults = allResultsHTML.slice(start, end);

    resultWrapper.innerHTML = "";
    pageResults.forEach((card) => resultWrapper.appendChild(card));

    renderPagination();
  }

  // ============================================================
  // ============================================================
  function renderPagination() {
    paginationWrapper.innerHTML = "";
    const totalPages = Math.ceil(allResultsHTML.length / RESULTS_PER_PAGE);
    if (totalPages <= 1) return;

    for (let i = 1; i <= totalPages; i++) {
      const btn = document.createElement("button");
      btn.textContent = i;
      btn.classList.toggle("active", i === currentPage);
      btn.addEventListener("click", () => renderPage(i));
      paginationWrapper.appendChild(btn);
    }
  }

  // ============================================================
  // ============================================================
  function escapeHTML(str) {
    return str
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  // ============================================================
  // ============================================================
  function getSelectedValues(category) {
    const wrapper = [...document.querySelectorAll(".category-wrapper")].find(
      (el) =>
        el.querySelector(".category-title-wrapper span:last-child")
          ?.textContent === category
    );
    if (!wrapper) return [];
    return [...wrapper.querySelectorAll("input[type='checkbox']:checked")].map(
      (cb) => cb.value
    );
  }

  // ============================================================
  // ============================================================
  document.body.addEventListener("click", (e) => {
    const card = e.target.closest(".result-card");
    if (card) {
      const thesisId = card.dataset.id;
      // Pass the row.number in URL if needed
      window.location.href = `${BASE_URL}view/pages/view-thesis.php?id=${thesisId}`;
    }
  });

  // ============================================================
  // ============================================================
  const sidebarObserver = new MutationObserver(() => {
    const checkboxes = document.querySelectorAll(
      "#side-bar input[type='checkbox']"
    );
    if (checkboxes.length > 0) {
      checkboxes.forEach((cb) => cb.addEventListener("change", fetchResults));
      fetchResults(); // Initial load
      sidebarObserver.disconnect();
    }
  });

  sidebarObserver.observe(document.body, { childList: true, subtree: true });

  // ============================================================
  // ============================================================
  const yearFilter = document.getElementById("year-filter");
  if (yearFilter) {
    yearFilter.addEventListener("change", () => {
      const selectedYear = yearFilter.value;
      filterByYear(selectedYear);
    });
  }

  // ============================================================
  // ============================================================
  function filterByYear(year) {
    if (!year) {
      renderPage(1);
      return;
    }

    const filtered = allResultsHTML.filter((card) => {
      const small = card.querySelector("small");
      if (!small) return false;
      const match = small.textContent.match(
        /Published:\s+([A-Za-z]+\s+\d{1,2},\s+(\d{4}))/
      );
      if (!match) return false;
      return match[2] === year;
    });

    resultWrapper.innerHTML = "";
    filtered.forEach((card) => resultWrapper.appendChild(card));

    paginationWrapper.innerHTML = ""; 
  }
});
