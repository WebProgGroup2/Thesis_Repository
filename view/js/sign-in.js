document.addEventListener("DOMContentLoaded", () => {
  const formWrapper = document.getElementById("form-wrapper");


  setTimeout(() => {
    formWrapper.classList.add("show");
  }, 100);
  
  document.getElementById("create-btn").addEventListener("click", () => {
    window.location.href = baseURL + "view/pages/create-account.php";
  });

  document.getElementById("back-btn").addEventListener("click", () => {
    window.location.href = baseURL + "index.php";
  });

  const form = document.getElementById("form-wrapper");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const schoolId = document.getElementById("school-id").value.trim();
    const password = document.getElementById("password").value.trim();

    if (!schoolId || !password) {
      alert("Please fill in both fields.");
      return;
    }

    try {
      const response = await fetch(baseURL + "controller/verify_account.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ school_id: schoolId, password }),
      });

      const result = await response.json();

      if (result.success) {
        alert(`Welcome, ${result.first_name.toUpperCase()}! Redirecting...`);

        // Set cookies and wait 
        try {
          const setRes = await fetch(`${baseURL}controller/set_cookies.php`, {
            method: "POST",
            credentials: 'same-origin',
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(result),
          });

          const setData = await setRes.json();
          if (setData.status === "success") {
            console.log("Cookies set via PHP!");
          } else {
            console.warn("set_cookies responded but did not return success:", setData);
          }
        } catch (err) {
          console.error("Error setting cookies:", err);
       
        }

        // Redirect based on role
        switch (result.role) {
          case "admin":
            window.location.href = baseURL + "view/pages/archive.php";
            break;
          case "faculty":
          case "student":
            window.location.href = baseURL + "index.php";
            break;
          default:
            window.location.href = baseURL + "index.php";
        }
      } else {
        alert(result.message || "Invalid School ID or Password");
      }
    } catch (err) {
      console.error("Error:", err);
      alert("Server error. Please try again later.");
    }
  });
});
