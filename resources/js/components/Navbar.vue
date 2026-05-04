<template>
  <div class="navbar">
    <div class="navbar-logo">
      <a href="#">Minimals</a>
    </div>
    <ul class="navbar-menu">
      <li><a href="#">Dashboard</a></li>
      <li><a href="#">Products</a></li>
      <li><a href="#">Customers</a></li>
      <li><a href="#">Reports</a></li>
    </ul>
  </div>
</template>

<style scoped>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background-color: #f0f2f5;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar-logo {
  font-size: 1.5rem;
  font-weight: bold;
}

.navbar-menu {
  list-style: none;
  display: flex;
  margin: 0;
  padding: 0;
}

.navbar-menu li {
  margin-left: 20px;
}

.navbar-menu a {
  text-decoration: none;
  color: #333;
}
</style>