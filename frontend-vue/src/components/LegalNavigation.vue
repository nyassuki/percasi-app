<template>
  <nav class="legal-navigation">
    <div class="container">
      <ul class="nav-list">
        <li class="nav-item">
          <router-link 
            to="/privacy-policy" 
            class="nav-link"
            active-class="active"
          >
            <i class="icon privacy-icon"></i>
            Kebijakan Privasi
          </router-link>
        </li>
        <li class="nav-item">
          <router-link 
            to="/terms-of-service" 
            class="nav-link"
            active-class="active"
          >
            <i class="icon terms-icon"></i>
            Syarat Layanan
          </router-link>
        </li>
        <li class="nav-item">
          <router-link 
            to="/cookie-policy" 
            class="nav-link"
            active-class="active"
          >
            <i class="icon cookie-icon"></i>
            Kebijakan Cookie
          </router-link>
        </li>
      </ul>
    </div>
  </nav>
</template>

<script>
export default {
  name: 'LegalNavigation',
  setup() {
    return {}
  }
}
</script>

<style scoped>
.legal-navigation {
  background: linear-gradient(135deg, #2c3e50 0%, #1a2530 100%);
  padding: 1rem 0;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  border-bottom: 3px solid #3498db;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.nav-list {
  display: flex;
  justify-content: center;
  list-style: none;
  margin: 0;
  padding: 0;
  gap: 2rem;
}

.nav-item {
  position: relative;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #ecf0f1;
  text-decoration: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.nav-link:hover {
  background: rgba(52, 152, 219, 0.2);
  color: #3498db;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
}

.nav-link.active {
  background: #3498db;
  color: white;
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.icon {
  width: 20px;
  height: 20px;
  display: inline-block;
  background-size: contain;
  background-repeat: no-repeat;
}

.privacy-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='currentColor'%3E%3Cpath d='M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z'/%3E%3C/svg%3E");
}

.terms-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='currentColor'%3E%3Cpath d='M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z'/%3E%3C/svg%3E");
}

.cookie-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='currentColor'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z'/%3E%3C/svg%3E");
}

@media (max-width: 768px) {
  .nav-list {
    flex-direction: column;
    gap: 0.5rem;
    align-items: center;
  }
  
  .nav-link {
    width: 280px;
    justify-content: center;
  }
}
</style>