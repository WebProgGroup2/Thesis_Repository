 const filterBtn = document.querySelector('.searchbar__filter');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    filterBtn.addEventListener('click', () => {
      dropdownMenu.style.display =
        dropdownMenu.style.display === 'block' ? 'none' : 'block';
    });

    // Close dropdown if clicked outside
    window.addEventListener('click', (e) => {
      if (!e.target.closest('.dropdown')) {
        dropdownMenu.style.display = 'none';
      }
    });