<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        <link rel="stylesheet" href="../css/home-style.css">
    </head>
    <body>
        <!-- Html section -->
        <header class="top-header">
            <div class="headr-logo"><a href="index.php"><img src="./assets/logo.png"></a></div>
            <nav class="text-nav">
                <a href="prodectpage.php?category=Computers">  <label>Computers</label></a>
                <a href="prodectpage.php?category=Furnitures"> <label>Furnitures</label></a>
                <a href="prodectpage.php?category=Accessories"><label>Accessories</label></a>
            </nav>
            <div class="search">
                <input type="search" id="searchBar" placeholder="Search anything...">
                <img src="assets/search.png">
                <div id="searchResults" class="results-box"></div>
            </div>
            <nav class="icon-nav">
                <a href="index.php"><img class="header-icon" src="assets/Homeicon.png"></a>
                <a href="cart.php"><img class="header-icon" style="width: 32px; position: relative; top: 4px;" src="assets/carticon.png"></a>
                <a href="contact_us.php"><img class="header-icon" src="assets/supporticon.png"></a>
                <?php
                  if(isset($_SESSION['user_logged_in'])){
                    echo '<a href="user_logout.php"><img class="header-icon" src="assets/logout.png" alt="logout icon"></a>';
                  }
                  else{
                    echo '<a href="user_signin.php"><img class="header-icon" src="assets/usericon.png"></a>';
                  }
                ?>
                
            </nav>
        </header>
        
        <!-- javaSecript section  -->
        <script>
            let products = [];
          
            // get prodect name
            fetch('includes/get_products.php')
              .then(response => response.json())
              .then(data => {
                products = data;
                console.log("products loaded:", products);
              })
              .catch(error => console.error("fetch error:", error));
          
            const searchBar = document.getElementById('searchBar');
            const resultsBox = document.getElementById('searchResults');
          
            searchBar.addEventListener('input', () => {
              const input = searchBar.value.toLowerCase().trim();
              resultsBox.innerHTML = '';
          
              if (input.length === 0) {
                resultsBox.style.display = 'none';
                return;
              }
          
              // Search product name
              const filtered = products.filter(product =>
                product.name.toLowerCase().includes(input)
              );
          
              if (filtered.length === 0) {
                resultsBox.style.display = 'none';
                return;
              }
          
              // Show the result 
              filtered.forEach(item => {
                const div = document.createElement('div');
                div.textContent = item.name.length > 20 ? item.name.substring(0, 20) + "..." : item.name;
                div.classList.add('search-item');
          
                // Event when click the database
                div.addEventListener('click', () => {
                  window.location.href = `details.php?id=${item.id}`;
                });
          
                resultsBox.appendChild(div);
              });
          
              resultsBox.style.display = 'block';
            });
          </script>
    </body>
</html>



