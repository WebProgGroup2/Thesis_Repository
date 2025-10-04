// search.js
document.addEventListener("DOMContentLoaded", () => {
  console.log("search.js loaded");

  const searchInput = document.getElementById("searchInput");
  const searchBtn = document.getElementById("searchBtn");
  const resultsContainer = document.getElementById("searchResults");
  const searchTagsContainer = document.getElementById("searchTags");

  if (!searchInput || !searchBtn || !resultsContainer) {
    console.error("Missing required DOM elements:", {
      searchInput: !!searchInput,
      searchBtn: !!searchBtn,
      resultsContainer: !!resultsContainer
    });
    return;
  }

  let containers = [];
  let currentPage = 1;
  const itemsPerPage = 6;

  // create or reuse cardWrapper and insert it BEFORE the resultsContainer
  let cardWrapper = document.getElementById("cardWrapper");
  if (!cardWrapper) {
    cardWrapper = document.createElement("div");
    cardWrapper.id = "cardWrapper";
    resultsContainer.parentNode.insertBefore(cardWrapper, resultsContainer);
  }

  // --- Rendering / Pagination ---
  function renderPage(results, page) {
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const paginatedItems = results.slice(start, end);

    // hide all containers first
    containers.forEach(c => (c.style.display = "none"));

    // show current page and renumber badges
    paginatedItems.forEach((c, i) => {
      c.style.display = "flex";
      const badge = c.querySelector(".badge");
      if (badge) badge.textContent = start + i + 1;
    });

    renderPagination(results.length, page);
  }

  function renderPagination(totalItems, page) {
    resultsContainer.innerHTML = "";
    const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));

    // prev button
    const prev = document.createElement("button");
    prev.textContent = "Prev";
    prev.className = "page-btn";
    prev.disabled = page === 1;
    prev.addEventListener("click", () => {
      if (page > 1) renderPage(Array.from(containers).filter(matchByQuery), page - 1);
    });
    resultsContainer.appendChild(prev);

    for (let i = 1; i <= totalPages; i++) {
      const btn = document.createElement("button");
      btn.textContent = i;
      btn.className = "page-btn";
      if (i === page) btn.classList.add("active");
      btn.addEventListener("click", () => {
        renderPage(Array.from(containers).filter(matchByQuery), i);
      });
      resultsContainer.appendChild(btn);
    }

    // next button
    const next = document.createElement("button");
    next.textContent = "Next";
    next.className = "page-btn";
    next.disabled = page === totalPages;
    next.addEventListener("click", () => {
      if (page < totalPages) renderPage(Array.from(containers).filter(matchByQuery), page + 1);
    });
    resultsContainer.appendChild(next);

    // add spacing under pagination so footer doesn't crowd it
    resultsContainer.style.marginBottom = "60px";
  }

  function matchByQuery(el) {
    const q = (searchInput.value || "").toLowerCase().trim();
    return q === "" || el.textContent.toLowerCase().includes(q);
  }

  // --- Fetch results from PHP ---
  function fetchResults(query) {
    const url = `search_api.php?q=${encodeURIComponent(query)}`;
    console.log("Fetching:", url);

    // show loading state
    cardWrapper.innerHTML = "<p>Loading...</p>";
    resultsContainer.innerHTML = "";

    return fetch(url, { cache: "no-store" })
      .then(resp => {
        if (!resp.ok) {
          throw new Error(`HTTP ${resp.status}`);
        }
        return resp.text(); // get text first to catch JSON parse errors
      })
      .then(text => {
        try {
          const data = JSON.parse(text);
          if (!Array.isArray(data)) {
            console.warn("Expected array, got:", data);
            throw new Error("Invalid JSON structure from server");
          }
          return data;
        } catch (err) {
          console.error("Invalid JSON response:", text);
          throw err;
        }
      });
  }

  // --- Build DOM cards from data array ---
  function buildCardsFromData(data) {
    cardWrapper.innerHTML = "";
    if (!data || data.length === 0) {
      cardWrapper.innerHTML = "<p>No results found.</p>";
      containers = [];
      renderPagination(0, 1);
      return;
    }

    data.forEach((item, index) => {
      const card = document.createElement("div");
      card.className = "card container";
      // use safe text insertion (avoid raw HTML injection)
      const title = escapeHtml(item.title || "");
      const authors = escapeHtml(item.authors || "");
      const abstract = escapeHtml(item.abstract || "");
      card.innerHTML = `
        <div class="badge">${index + 1}</div>
        <div class="card-content">
          <div class="title">${title}</div>
          <div class="authors">${authors}</div>
          <div class="abstract">${abstract}</div>
        </div>`;
      cardWrapper.appendChild(card);
    });

    containers = Array.from(document.querySelectorAll(".container"));
  }

  // --- Highlight helper ---
  function highlightAll(query) {
    if (!query) {
      containers.forEach(c => {
        resetHighlight(c.querySelector(".title"));
        resetHighlight(c.querySelector(".authors"));
        resetHighlight(c.querySelector(".abstract"));
      });
      return;
    }
    containers.forEach(c => {
      highlightText(c.querySelector(".title"), query);
      highlightText(c.querySelector(".authors"), query);
      highlightText(c.querySelector(".abstract"), query);
    });
  }

  // --- Utilities ---
  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function highlightText(element, query) {
    if (!element) return;
    const text = element.textContent;
    const regex = new RegExp(`(${escapeRegExp(query)})`, "gi");
    element.innerHTML = text.replace(regex, `<mark>$1</mark>`);
  }

  function resetHighlight(element) {
    if (!element) return;
    element.innerHTML = element.textContent;
  }

  function escapeRegExp(s) {
    return s.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  // --- Main search flow ---
  function performSearch() {
  const q = searchInput.value.trim();
  if (q) {
    renderSearchTag(q);
  }
  fetchResults(q)
    .then(data => {
      console.log("Received", data.length, "rows");
      buildCardsFromData(data);
      highlightAll(q);
      currentPage = 1;
      renderPage(Array.from(containers), currentPage);
    })
    .catch(err => {
      console.error("Search error:", err);
      cardWrapper.innerHTML = "<p>Error loading results. Check console/network.</p>";
    });
}

  function renderSearchTag(text) {
  // Clear previous tags
  searchTagsContainer.innerHTML = "";

  const tag = document.createElement("div");
  tag.className = "search-tag";

  const remove = document.createElement("span");
  remove.className = "remove-tag";
  remove.textContent = "×";
  remove.addEventListener("click", () => {
    searchTagsContainer.innerHTML = "";
    searchInput.value = "";
    performSearch();
  });

  const label = document.createElement("span");
  label.textContent = text;

  tag.appendChild(remove);
  tag.appendChild(label);
  searchTagsContainer.appendChild(tag);
  }
  // events
  searchBtn.addEventListener("click", (e) => {
    e.preventDefault();
    performSearch();
  });

  searchInput.addEventListener("keyup", (e) => {
    // Enter triggers search immediately
    if (e.key === "Enter") {
      e.preventDefault();
      performSearch();
    } else {
      // live highlight while typing (still must fetch to update results)
      // you can uncomment auto-performSearch() for live fetch on each keystroke (rate-limit recommended)
      // performSearch();
    }
  });

  // initial load: load all results
  performSearch();
});