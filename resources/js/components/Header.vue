<template>
  <div class="header">
    <div class="header-left">
      <button class="menu-button" @click="toggleSidebar">
        <span class="menu-icon"></span>
      </button>
    </div>
    <div class="header-center">
      <input type="text" placeholder="Search..." class="search-input" />
    </div>
    <div class="header-right">
      <button class="icon-button">
        <i class="fa fa-bell"></i>
      </button>
      <button class="icon-button">
        <i class="fa fa-envelope"></i>
      </button>
      <button class="icon-button">
        <i class="fa fa-user"></i>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    toggleSidebar() {
      this.$emit('toggle-sidebar'); // Emit an event to the parent component
    }
  }
};
</script>

<style scoped>
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background-color: #fff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.header-left {
  display: flex;
  align-items: center;
}

.menu-button {
  background-color: transparent;
  border: none;
  padding: 10px;
  cursor: pointer;
}

.menu-icon {
  display: block;
  width: 24px;
  height: 2px;
  background-color: #333;
  margin: 6px 0;
}

.header-center {
  flex-grow: 1;
  margin: 0 20px;
}

.search-input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

.header-right {
  display: flex;
  align-items: center;
}

.icon-button {
  background-color: transparent;
  border: none;
  padding: 10px;
  cursor: pointer;
  margin-left: 10px;
}
</style>