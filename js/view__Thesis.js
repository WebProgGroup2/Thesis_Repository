// Thesis data (will be replaced by PHP later)
const thesisListData = [
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  }
  ,
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  }
  ,
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  },
  {
    title: "AI-Powered Chatbot for Student Academic Advising",
    author: "Maria Isabel Cruz",
    date: "01-00-21",
    place: "National University Laguna",
    methodology: "Qualitative"
  }

];

// Render thesis data into the table
function renderThesisList() {
  const tableBody = document.getElementById("thesis-table-body");
  tableBody.innerHTML = "";

  thesisListData.forEach(thesis => {
    const row = document.createElement("tr");
    row.classList.add("thesis-table__row");

    row.innerHTML = `
      <td class="thesis-table__cell">${thesis.title}</td>
      <td class="thesis-table__cell">${thesis.author}</td>
      <td class="thesis-table__cell">${thesis.date}</td>
      <td class="thesis-table__cell">${thesis.place}</td>
      <td class="thesis-table__cell">${thesis.methodology}</td>
      <td class="thesis-table__cell">
        <div class="thesis-table__actions">
          <button class="thesis-table__button thesis-table__button--edit">
            <i class="fa-solid fa-pen"></i>
          </button>
          <button class="thesis-table__button thesis-table__button--view">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
      </td>
    `;
    tableBody.appendChild(row);
  });
}

// Initialize when DOM loads
document.addEventListener("DOMContentLoaded", renderThesisList);
