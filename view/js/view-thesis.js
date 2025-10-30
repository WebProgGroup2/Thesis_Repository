document.addEventListener("DOMContentLoaded", async () => {
  const BASE_URL = `${window.location.origin}/thesis_repo/`;
  const urlParams = new URLSearchParams(window.location.search);
  const thesisId = urlParams.get("id");

  // Prev/Next buttons
  const prevBtn = document.querySelector(".prev-btn");
  const nextBtn = document.querySelector(".next-btn");

  if (!thesisId) {
    console.error("Missing thesis ID in URL");
    return;
  }

  try {
    const response = await fetch(
      `../../controller/get_thesis.php?id=${thesisId}`
    );
    const thesis = await response.json();

    console.log(thesis);

    if (thesis.error) {
      console.error(thesis.error);
      return;
    }

    // Title
    document.getElementById("thesis-title").textContent =
      thesis.title || "Untitled Thesis";

    // Author (only 1)
    const authorWrapper = document.getElementById("author-wrapper");
    authorWrapper.innerHTML = "";

    if (thesis.authors && thesis.authors.length > 0) {
      const span = document.createElement("span");
      span.classList.add("authors");
      span.textContent =
        thesis.authors[0].firstname +
        " " +
        thesis.authors[0].middlename +
        " " +
        thesis.authors[0].lastname;
      authorWrapper.appendChild(span);
    } else {
      const span = document.createElement("span");
      span.classList.add("authors");
      span.textContent = "Unknown Author";
      authorWrapper.appendChild(span);
    }

    // Publication date
    const pubDate = new Date(thesis.pub_date);
    document.getElementById("pub-date").textContent = !isNaN(pubDate)
      ? pubDate.toLocaleDateString("en-US", {
          year: "numeric",
          month: "long",
          day: "numeric",
        })
      : thesis.pub_date || "Unknown Date";

    // Safe display of abstract with HTML sanitization
    const abstractContent = document.getElementById("abstract-content");
    if (thesis.abstract) {
      abstractContent.innerHTML = sanitizeAbstract(thesis.abstract);
    } else {
      abstractContent.textContent = "No abstract available.";
    }

    // HTML sanitization function
    function sanitizeAbstract(html) {
      const temp = document.createElement("div");
      temp.innerHTML = html;

      // Remove dangerous elements
      const dangerous = temp.querySelectorAll(
        "script, style, link, meta, iframe, object, embed, form, input, button, textarea, select"
      );
      dangerous.forEach((el) => el.remove());

      // Remove event handlers and dangerous attributes
      const allElements = temp.querySelectorAll("*");
      allElements.forEach((el) => {
        Array.from(el.attributes).forEach((attr) => {
          if (
            attr.name.startsWith("on") || // Remove onclick, onload, etc.
            ["src", "href", "action", "xmlns"].includes(attr.name.toLowerCase())
          ) {
            el.removeAttribute(attr.name);
          }
        });
      });

      return temp.innerHTML;
    }

    // Methodology
    document.getElementById("methodology-content").textContent =
      thesis.methodology || "N/A";

    //Thesis types
    const thesisTypeContainer = document.getElementById("thesis-type-content");
    thesisTypeContainer.innerHTML = thesis.thesis_types_name?.length
      ? thesis.thesis_types_name
      : "No thesis type available";

    // Keywords
    const keywordsContainer = document.getElementById("keywords-content");
    keywordsContainer.innerHTML = thesis.keywords?.length
      ? thesis.keywords.join(", ")
      : "No keywords available.";

    // Publication Place
    document.getElementById("place").textContent =
      thesis.pub_place || "Unknown";

    // Co-Authors
    const coAuthorList = document.querySelector(
      "#right-section #co-author-label + .list"
    );
    coAuthorList.innerHTML = "";
    (thesis.authors || []).slice(1).forEach((author) => {
      const span = document.createElement("span");
      span.classList.add("name");
      span.textContent =
        author.firstname + " " + author.middlename + " " + author.lastname;
      coAuthorList.appendChild(span);
    });
    if (coAuthorList.children.length === 0) {
      const span = document.createElement("span");
      span.classList.add("name");
      span.textContent = "No co-authors";
      coAuthorList.appendChild(span);
    }

    // References (limit to 5)
    const refList = document.querySelector("#references-label + .list");
    refList.innerHTML = "";
    const references = thesis.references_list?.slice(0, 3) || [];
    if (references.length > 0) {
      references.forEach((ref) => {
        const span = document.createElement("span");
        span.classList.add("reference");
        span.textContent = ref;
        refList.appendChild(span);
      });
      if (thesis.references_list.length > 3) {
        const more = document.createElement("span");
        more.classList.add("reference", "more-ref");
        more.textContent = `+${thesis.references_list.length - 3} more...`;
        refList.appendChild(more);
      }
    } else {
      const noRef = document.createElement("span");
      noRef.classList.add("reference");
      noRef.textContent = "No references available.";
      refList.appendChild(noRef);
    }

    // Read button
    const readBtn = document.getElementById("read-thesis-btn");

    // Helper function to reset button state
    const resetButton = () => {
      readBtn.textContent = "Read Thesis";
      readBtn.disabled = false;
    };

    if (thesis && thesis.file_location && thesis.thesis_id) {
      const isPDF = thesis.file_location.toLowerCase().endsWith(".pdf");

      if (isPDF) {
        readBtn.onclick = async () => {
          readBtn.textContent = "Opening...";
          readBtn.disabled = true;

          try {
            // Use PHP viewer for better control
            const url = `${baseURL}controller/pdf-viewer.php?thesis_id=${
              thesis.thesis_id
            }&file=${encodeURIComponent(thesis.file_location)}`;
            window.open(url, "_blank");
          } catch (error) {
            console.error("Failed to open PDF:", error);
            readBtn.textContent = "Error opening file";
          } finally {
            // Only reset if there was no error text
            if (readBtn.textContent === "Opening...") {
              setTimeout(resetButton, 1000);
            }
          }
        };
      } else {
        readBtn.disabled = true;
        readBtn.textContent = "Invalid file format";
      }
    } else {
      readBtn.disabled = true;
      if (!thesis?.file_location) {
        readBtn.textContent = "File not available";
      } else if (!thesis?.thesis_id) {
        readBtn.textContent = "Thesis ID missing";
      }
    }

    // Cover image
    const coverImg = document.getElementById("thesis-cover");
    coverImg.src = thesis.thesis_cover
      ? baseURL + thesis.thesis_cover
      : baseURL + "/view/assets/placeholder.jpg";

    // Page number
    const pageNumber = document.getElementById("page-number");
    pageNumber.textContent = thesis.page_count
      ? `Page count: ${thesis.page_count}`
      : "Page count: N/A";

    // SESSION STORAGE NAVIGATION
    const searchResults = JSON.parse(
      sessionStorage.getItem("searchResults") || "[]"
    );

    // Find current index
    const currentIndex = searchResults.findIndex(
      (item) => item.id === thesisId
    );

    const thesisNumberEl = document.getElementById("thesis-number");
    if (currentIndex !== -1) {
      thesisNumberEl.textContent = searchResults[currentIndex].number;
    }

    // Prev button
    prevBtn.onclick = () => {
      if (currentIndex > 0) {
        const prevId = searchResults[currentIndex - 1].id;
        window.location.href = `${BASE_URL}view/pages/view-thesis.php?id=${prevId}`;
      }
    };

    // Next button
    nextBtn.onclick = () => {
      if (currentIndex < searchResults.length - 1) {
        const nextId = searchResults[currentIndex + 1].id;
        window.location.href = `${BASE_URL}view/pages/view-thesis.php?id=${nextId}`;
      }
    };

    // disable buttons at boundaries
    prevBtn.disabled = currentIndex <= 0;
    nextBtn.disabled = currentIndex >= searchResults.length - 1;
  } catch (error) {
    console.error("Error fetching thesis data:", error);
  }
});
