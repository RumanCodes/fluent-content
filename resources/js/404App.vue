<template>
  <div class="error-page">
    <div class="error-content">
      <div class="error-icon">
        <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </div>

      <h1 class="error-code">404</h1>
      <h2 class="error-title">Page Not Found</h2>
      <p class="error-message">
        The page you're looking for doesn't exist or has been moved.
      </p>

    </div>
  </div>
</template>

<style scoped>
.error-page {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  width: 100%;
  margin: -.7rem -1.3rem 0 -1.4rem;
}

.error-content {
  text-align: center;
  max-width: 500px;
}

.error-icon {
  display: flex;
  justify-content: center;
  margin-bottom: 2rem;
  color: #2196f3;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.05);
  }
}

.error-code {
  font-size: 6rem;
  font-weight: 700;
  margin: 0 0 1rem 0;
  color: #1d1d1f;
  line-height: 1;
}

.error-title {
  font-size: 2rem;
  font-weight: 600;
  margin: 0 0 1rem 0;
  color: #1d1d1f;
}

.error-message {
  font-size: 1.125rem;
  color: #6e6e73;
  margin: 0 0 2.5rem 0;
  line-height: 1.6;
}
@media (max-width: 640px) {
  .error-code {
    font-size: 4rem;
  }

  .error-title {
    font-size: 1.5rem;
  }

  .error-message {
    font-size: 1rem;
  }
}
</style>
