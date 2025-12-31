<template>
  <div class="dashboard">
    <div class="header">
      <h1>Dashboard</h1>
      <p class="subtitle-fc">Welcome back! Here's an overview of your AI-generated content.</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-content">
          <div class="stat-label">Total Posts</div>
          <div class="stat-value">{{ stats.total || 0 }}</div>
        </div>
        <div class="stat-icon blue">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
          </svg>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-content">
          <div class="stat-label">Scheduled</div>
          <div class="stat-value">{{ stats.scheduled || 0 }}</div>
        </div>
        <div class="stat-icon orange">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-content">
          <div class="stat-label">Published</div>
          <div class="stat-value">{{ stats.published || 0 }}</div>
        </div>
        <div class="stat-icon green">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
        </div>
      </div>

<!--      <div class="stat-card">-->
<!--        <div class="stat-content">-->
<!--          <div class="stat-label">Private</div>-->
<!--          <div class="stat-value">{{ stats.private || 0 }}</div>-->
<!--        </div>-->
<!--        <div class="stat-icon purple">-->
<!--          <svg width="24" height="24" viewBox="0 0 24 24" fill="none"-->
<!--               stroke="currentColor" stroke-width="2">-->
<!--            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>-->
<!--            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>-->
<!--          </svg>-->
<!--        </div>-->
<!--      </div>-->

<!--      <div class="stat-card">-->
<!--        <div class="stat-content">-->
<!--          <div class="stat-label">Pending</div>-->
<!--          <div class="stat-value">{{ stats.pending || 0 }}</div>-->
<!--        </div>-->
<!--        <div class="stat-icon orange">-->
<!--          <svg width="24" height="24" viewBox="0 0 24 24" fill="none"-->
<!--               stroke="currentColor" stroke-width="2">-->
<!--            <circle cx="12" cy="12" r="10"/>-->
<!--            <polyline points="12 6 12 12 16 14"/>-->
<!--          </svg>-->
<!--        </div>-->
<!--      </div>-->

<!--      <div class="stat-card">-->
<!--        <div class="stat-content">-->
<!--          <div class="stat-label">Draft</div>-->
<!--          <div class="stat-value">{{ stats.draft || 0 }}</div>-->
<!--        </div>-->
<!--        <div class="stat-icon lavenderblush">-->
<!--          <svg width="24" height="24" viewBox="0 0 24 24" fill="none"-->
<!--               stroke="currentColor" stroke-width="2">-->
<!--            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>-->
<!--            <polyline points="14 2 14 8 20 8"/>-->
<!--            <line x1="16" y1="13" x2="8" y2="13"/>-->
<!--            <line x1="16" y1="17" x2="8" y2="17"/>-->
<!--          </svg>-->
<!--        </div>-->
<!--      </div>-->


      <div class="stat-card">
        <div class="stat-content">
          <div class="stat-label">This Month</div>
          <div class="stat-value">{{ stats.thisMonth || 0 }}</div>
        </div>
        <div class="stat-icon purple">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="content-grid">
      <!-- Recent Posts -->
      <div class="panel">
        <h2>Recent Posts</h2>
        <div class="posts-list">
          <div v-for="post in recent" :key="post.ID" class="post-item">
            <div class="post-title">{{ post.post_title }}</div>
            <div class="post-meta">
              <span :class="['status-badge', post.post_status === 'publish' ? 'published' : 'scheduled']">
                {{ post.post_status === 'publish' ? 'Published' : 'Scheduled' }}
              </span>
              <span class="post-time">{{ post.time_ago }}</span>
              <span v-if="post.category" class="post-category">{{ post.category }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Upcoming Scheduled -->
        <div class="panel">
          <h2>Upcoming Scheduled</h2>
          <div class="scheduled-list">
            <div v-for="scheduled in upcomingScheduled" :key="scheduled.ID" class="scheduled-item">
              <div class="scheduled-title">{{ scheduled.post_title }}</div>
              <div class="scheduled-time">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <polyline points="12 6 12 12 16 14"/>
                </svg>
                {{ scheduled.scheduled_time }}
              </div>
              <div class="scheduled-tags">
                <span v-for="tag in scheduled.tags" :key="tag" class="tag">{{ tag }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="panel">
          <h2>Quick Stats</h2>
          <div class="quick-stat">
            <div class="quick-stat-label">Words Generated</div>
            <div class="quick-stat-value">{{ formatNumber(quickStats.wordsGenerated) }}</div>
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: '85%' }"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "DashboardApp",
  data() {
    return {
      stats: {
        total: 0,
        scheduled: 0,
        published: 0,
        private: 0,
        pending: 0,
        draft: 0,
        thisMonth: 0,
      },
      recent: [],
      upcomingScheduled: [],
      quickStats: {
        wordsGenerated: 124500
      }
    };
  },
  methods: {
    formatNumber(num) {
      if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
      }
      return num.toString();
    }
  },
  mounted() {
    // fetch(fluentContentObject.ajaxurl + '?action=fluent_content_post_data')
    //     .then(r => r.json())
    //     .then(data => {
    //       this.stats = {
    //         total: data.stats.total || 247,
    //         scheduled: data.stats.scheduled || 18,
    //         published: data.stats.published || 229,
    //         thisMonth: data.stats.thisMonth || 34
    //       };
    //       this.recent = data.recent || [];
    //       this.upcomingScheduled = data.upcoming || [];
    //     });

    fetch(fluentContentObject.ajaxurl + '?action=fluent_content_post_data')
        .then(r => r.json())
        .then(response => {
          if (response.success) {
            const data = response.data;
            this.stats = data.stats;
            this.recent = data.recent;
            this.upcomingScheduled = data.upcoming;
            this.quickStats = data.quickStats;
          }
        })
        .catch(error => {
          console.error('Error fetching dashboard data:', error);
        });
  }
};
</script>

<style scoped>
.dashboard {
  max-width: 100%;
  padding: 2rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  background: #f5f5f7;
  min-height: 100vh;
  margin: -.7rem -1.3rem 0 -1.4rem;
}

.header {
  margin-bottom: 2rem;
}

.header h1 {
  font-size: 2rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
  color: #1d1d1f;
}

.subtitle-fc {
  color: #6e6e73;
  font-size: 1rem;
  margin: 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: #6e6e73;
  margin-bottom: 0.5rem;
}

.stat-value {
  font-size: 2rem;
  font-weight: 600;
  color: #1d1d1f;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon.blue {
  background: #e3f2fd;
  color: #2196f3;
}

.stat-icon.orange {
  background: #fff3e0;
  color: #ff9800;
}

.stat-icon.green {
  background: #e8f5e9;
  color: #4caf50;
}

.stat-icon.purple {
  background: #f3e5f5;
  color: #9c27b0;
}

.content-grid {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 1.5rem;
}

@media (max-width: 1024px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
}

.panel {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
}

.panel h2 {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0 0 1.5rem 0;
  color: #1d1d1f;
}

.posts-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.post-item {
  padding: 1.25rem;
  border: 1px solid #e5e5ea;
  border-radius: 8px;
  transition: all 0.2s;
}

.post-item:hover {
  border-color: #2196f3;
  box-shadow: 0 2px 8px rgba(33, 150, 243, 0.15);
}

.post-title {
  font-size: 1rem;
  font-weight: 500;
  color: #1d1d1f;
  margin-bottom: 0.75rem;
}

.post-meta {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
  font-size: 0.875rem;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
}

.status-badge.published {
  background: #e8f5e9;
  color: #2e7d32;
}

.status-badge.published::before {
  content: '● ';
}

.status-badge.scheduled {
  background: #fff3e0;
  color: #e65100;
}

.status-badge.scheduled::before {
  content: '● ';
}

.post-time {
  color: #6e6e73;
}

.post-category {
  background: #f5f5f7;
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
  color: #6e6e73;
}

.sidebar {
  display: flex;
  flex-direction: column;
}

.scheduled-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.scheduled-item {
  padding-bottom: 1.25rem;
  border-bottom: 1px solid #e5e5ea;
}

.scheduled-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.scheduled-title {
  font-size: 0.9375rem;
  font-weight: 500;
  color: #1d1d1f;
  margin-bottom: 0.5rem;
}

.scheduled-time {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.8125rem;
  color: #6e6e73;
  margin-bottom: 0.5rem;
}

.scheduled-time svg {
  flex-shrink: 0;
}

.scheduled-tags {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.tag {
  font-size: 0.75rem;
  color: #2196f3;
  background: #e3f2fd;
  padding: 0.25rem 0.625rem;
  border-radius: 4px;
}

.quick-stat {
  padding: 1rem 0;
}

.quick-stat-label {
  font-size: 0.875rem;
  color: #6e6e73;
  margin-bottom: 0.5rem;
}

.quick-stat-value {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1d1d1f;
  margin-bottom: 0.75rem;
}

.progress-bar {
  width: 100%;
  height: 6px;
  background: #e5e5ea;
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #2196f3;
  border-radius: 3px;
  transition: width 0.3s ease;
}
</style>
