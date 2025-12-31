<template>
  <div class="generate-content">
    <div class="header">
      <h1>Generate Content</h1>
      <p class="subtitle">Create AI-powered content with just a few keywords.</p>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button
          :class="['tab', { active: activeTab === 'single' }]"
          @click="activeTab = 'single'"
      >
        Single Post
      </button>
      <button
          :class="['tab', { active: activeTab === 'bulk' }]"
          @click="activeTab = 'bulk'"
      >
        Bulk Generate
      </button>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
      <!-- Left Panel - Form -->
      <div class="form-panel">
        <!-- Content Type -->
        <div class="form-section">
          <label class="form-label">Content Type</label>
          <div class="content-type-grid">
            <button
                :class="['type-card', { selected: selectedType === 'blog' }]"
                @click="selectedType = 'blog'"
            >
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <line x1="10" y1="9" x2="8" y2="9"/>
              </svg>
              <span>Blog Post</span>
            </button>

            <button
                :class="['type-card', { selected: selectedType === 'article' }]"
                @click="selectedType = 'article'"
            >
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
              </svg>
              <span>Article</span>
            </button>

            <button
                :class="['type-card', { selected: selectedType === 'product' }]"
                @click="selectedType = 'product'"
            >
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                <line x1="12" y1="22.08" x2="12" y2="12"/>
              </svg>
              <span>Product Description</span>
            </button>
          </div>
        </div>

        <!-- Keywords -->
        <div class="form-section">
          <label class="form-label">
            Keywords <span class="required">*</span>
          </label>
          <input
              type="text"
              v-model="keywords"
              class="form-input"
              placeholder="e.g., Digital Marketing, SEO, Social Media"
          />
          <p class="form-hint">Separate multiple keywords with commas</p>
        </div>

        <!-- Title -->
        <div class="form-section">
          <label class="form-label">Title (Optional)</label>
          <input
              type="text"
              v-model="title"
              class="form-input"
              placeholder="Leave empty for AI to generate"
          />
        </div>

        <!-- Advanced Options -->
        <div class="form-section">
          <button
              class="advanced-toggle"
              @click="showAdvanced = !showAdvanced"
          >
            <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                :class="{ rotated: showAdvanced }"
            >
              <polyline points="6 9 12 15 18 9"/>
            </svg>
            Advanced Options
          </button>

          <div v-if="showAdvanced" class="advanced-content">
            <div class="form-group">
              <label class="form-label-small">Tone</label>
              <select v-model="tone" class="form-select">
                <option value="professional">Professional</option>
                <option value="casual">Casual</option>
                <option value="friendly">Friendly</option>
                <option value="formal">Formal</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label-small">Word Count</label>
              <select v-model="wordCount" class="form-select">
                <option value="500">500 words</option>
                <option value="800">800 words</option>
                <option value="1000">1000 words</option>
                <option value="1500">1500 words</option>
                <option value="2000">2000+ words</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label-small">Language</label>
              <select v-model="language" class="form-select">
                <option value="english">English</option>
                <option value="spanish">Spanish</option>
                <option value="french">French</option>
                <option value="german">German</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label-small">Creativity</label>
              <select v-model="creativity" class="form-select">
                <option value="low">Low (More Focused)</option>
                <option value="medium">Medium (Balanced)</option>
                <option value="high">High (More Creative)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <button 
            class="btn-primary" 
            @click="generateContent"
            :disabled="generating || !keywords.trim()"
          >
            <svg v-if="!generating" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2L2 7l10 5 10-5-10-5z"/>
              <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
            <span v-if="generating" class="spinner"></span>
            {{ generating ? 'Generating...' : 'Generate Content' }}
          </button>
          <button 
            class="btn-secondary"
            @click="saveAsDraft"
            :disabled="saving || !generatedContent"
          >
            {{ saving ? 'Saving...' : 'Save as Draft' }}
          </button>
        </div>

        <!-- Generated Content Preview -->
        <div v-if="generatedContent" class="generated-content-panel">
          <div class="preview-header">
            <h3>Generated Content Preview</h3>
            <button class="btn-close" @click="clearGeneratedContent">×</button>
          </div>
          
          <div class="preview-meta">
            <div class="meta-item">
              <strong>Title:</strong> {{ generatedTitle || 'Untitled' }}
            </div>
            <div class="meta-item">
              <strong>Word Count:</strong> {{ generatedWordCount }}
            </div>
            <div class="meta-item" v-if="generatedModel">
              <strong>Model:</strong> {{ generatedModel }}
            </div>
          </div>

          <div class="preview-content" v-html="generatedContent"></div>
        </div>

        <!-- Success/Error Messages -->
        <div v-if="message.text" :class="['message', message.type]">
          {{ message.text }}
          <button class="message-close" @click="message.text = ''">×</button>
        </div>
      </div>

      <!-- Right Sidebar -->
      <div class="sidebar">
        <!-- Tips Panel -->
        <div class="panel tips-panel">
          <h2>Tips for Better Content</h2>
          <div class="tips-list">
            <div class="tip-item">
              <div class="tip-number">1</div>
              <div class="tip-content">
                <h3>Be Specific</h3>
                <p>Use detailed keywords to get more relevant content</p>
              </div>
            </div>

            <div class="tip-item">
              <div class="tip-number">2</div>
              <div class="tip-content">
                <h3>Use Context</h3>
                <p>Add additional information to guide the AI</p>
              </div>
            </div>

            <div class="tip-item">
              <div class="tip-number">3</div>
              <div class="tip-content">
                <h3>Review & Edit</h3>
                <p>Always review and customize generated content</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Generations -->
        <div class="panel recent-panel">
          <h2>Recent Generations</h2>
          <div class="recent-list">
            <div
                v-for="item in recentGenerations"
                :key="item.id"
                class="recent-item"
            >
              <div class="recent-title">{{ item.title }}</div>
              <div class="recent-time">{{ item.time }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "GenerateContentPage",
  data() {
    return {
      activeTab: 'single',
      selectedType: 'blog',
      keywords: '',
      title: '',
      showAdvanced: false,
      tone: 'professional',
      wordCount: '1000',
      language: 'english',
      creativity: 'medium',
      generating: false,
      saving: false,
      generatedContent: '',
      generatedTitle: '',
      generatedWordCount: 0,
      generatedModel: '',
      generatedMetaDescription: '',
      message: {
        text: '',
        type: ''
      },
      recentGenerations: []
    };
  },
  mounted() {
    this.loadRecentGenerations();
  },
  methods: {
    async generateContent() {
      if (!this.keywords.trim()) {
        this.showMessage('Please enter keywords to generate content', 'error');
        return;
      }

      this.generating = true;
      this.generatedContent = '';
      this.message.text = '';

      try {
        const response = await fetch(
          `${fluentContentObject.ajaxurl}?action=fluent_content_generate`,
          {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              nonce: fluentContentObject.nonce,
              keywords: this.keywords,
              title: this.title,
              contentType: this.selectedType,
              wordCount: parseInt(this.wordCount),
              tone: this.tone,
              language: this.language,
              creativity: this.creativity
            })
          }
        );

        const result = await response.json();

        if (result.success) {
          this.generatedContent = result.data.content;
          this.generatedTitle = result.data.title;
          this.generatedWordCount = result.data.wordCount;
          this.generatedModel = result.data.model;
          this.generatedMetaDescription = result.data.metaDescription || '';
          
          // Update title if it was auto-generated
          if (!this.title && result.data.title) {
            this.title = result.data.title;
          }

          this.showMessage('Content generated successfully!', 'success');
          
          // Scroll to generated content
          this.$nextTick(() => {
            const panel = document.querySelector('.generated-content-panel');
            if (panel) {
              panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
          });
        } else {
          this.showMessage(result.data.message || 'Failed to generate content', 'error');
        }
      } catch (error) {
        console.error('Error generating content:', error);
        this.showMessage('Error generating content: ' + error.message, 'error');
      } finally {
        this.generating = false;
      }
    },

    async saveAsDraft() {
      if (!this.generatedContent) {
        this.showMessage('Please generate content first', 'error');
        return;
      }

      if (!this.generatedTitle && !this.title) {
        this.showMessage('Please provide a title for the post', 'error');
        return;
      }

      this.saving = true;

      try {
        const response = await fetch(
          `${fluentContentObject.ajaxurl}?action=fluent_content_save_post`,
          {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              nonce: fluentContentObject.nonce,
              title: this.generatedTitle || this.title,
              content: this.generatedContent,
              metaDescription: this.generatedMetaDescription,
              status: 'draft'
            })
          }
        );

        const result = await response.json();

        if (result.success) {
          this.showMessage('Post saved as draft successfully!', 'success');
          
          // Reload recent generations
          this.loadRecentGenerations();
          
          // Optionally clear the form after a delay
          setTimeout(() => {
            if (confirm('Would you like to clear the form and generate new content?')) {
              this.clearForm();
            }
          }, 2000);
        } else {
          this.showMessage(result.data.message || 'Failed to save post', 'error');
        }
      } catch (error) {
        console.error('Error saving post:', error);
        this.showMessage('Error saving post: ' + error.message, 'error');
      } finally {
        this.saving = false;
      }
    },

    async loadRecentGenerations() {
      try {
        // This would ideally come from an API endpoint
        // For now, we'll use a placeholder
        const response = await fetch(
          `${fluentContentObject.ajaxurl}?action=fluent_content_post_data`,
          {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json'
            }
          }
        );

        const result = await response.json();
        
        if (result.success && result.data.recent) {
          this.recentGenerations = result.data.recent.slice(0, 3).map(post => ({
            id: post.ID,
            title: post.post_title,
            time: post.time_ago
          }));
        }
      } catch (error) {
        console.error('Error loading recent generations:', error);
        // Fallback to empty array
        this.recentGenerations = [];
      }
    },

    clearGeneratedContent() {
      this.generatedContent = '';
      this.generatedTitle = '';
      this.generatedWordCount = 0;
      this.generatedModel = '';
      this.generatedMetaDescription = '';
    },

    clearForm() {
      this.keywords = '';
      this.title = '';
      this.clearGeneratedContent();
      this.showAdvanced = false;
    },

    showMessage(text, type) {
      this.message = { text, type };
      setTimeout(() => {
        this.message.text = '';
      }, 5000);
    }
  }
};
</script>

<style scoped>
.generate-content {
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

.subtitle {
  color: #6e6e73;
  font-size: 1rem;
  margin: 0;
}

/* Tabs */
.tabs {
  display: flex;
  gap: 0;
  margin-bottom: 2rem;
  border-bottom: 2px solid #e5e5ea;
}

.tab {
  padding: 0.875rem 1.5rem;
  background: none;
  border: none;
  color: #6e6e73;
  font-size: 0.9375rem;
  font-weight: 500;
  cursor: pointer;
  position: relative;
  transition: color 0.2s;
}

.tab:hover {
  color: #1d1d1f;
}

.tab.active {
  color: #2196f3;
}

.tab.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 2px;
  background: #2196f3;
}

/* Content Grid */
.content-grid {
  display: grid;
  grid-template-columns: 1fr 450px;
  gap: 1.5rem;
}

@media (max-width: 1024px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
}

/* Form Panel */
.form-panel {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-section {
  margin-bottom: 2rem;
}

.form-label {
  display: block;
  font-size: 0.9375rem;
  font-weight: 500;
  color: #1d1d1f;
  margin-bottom: 0.75rem;
}

.required {
  color: #f44336;
}

/* Content Type Cards */
.content-type-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.type-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 1.5rem 1rem;
  background: #f8f9fa;
  border: 2px solid #e5e5ea;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  color: #6e6e73;
}

.type-card:hover {
  background: #f0f0f2;
  border-color: #d1d1d6;
}

.type-card.selected {
  background: #e3f2fd;
  border-color: #2196f3;
  color: #2196f3;
}

.type-card svg {
  flex-shrink: 0;
}

.type-card span {
  font-size: 0.875rem;
  font-weight: 500;
}

/* Form Inputs */
.form-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 1px solid #e5e5ea;
  border-radius: 8px;
  font-size: 0.9375rem;
  color: #1d1d1f;
  transition: all 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #2196f3;
  box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
}

.form-input::placeholder {
  color: #a1a1a6;
}

.form-hint {
  margin-top: 0.5rem;
  font-size: 0.8125rem;
  color: #6e6e73;
}

/* Advanced Options */
.advanced-toggle {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: none;
  border: none;
  color: #2196f3;
  font-size: 0.9375rem;
  font-weight: 500;
  cursor: pointer;
  padding: 0;
  transition: opacity 0.2s;
}

.advanced-toggle:hover {
  opacity: 0.8;
}

.advanced-toggle svg {
  transition: transform 0.2s;
}

.advanced-toggle svg.rotated {
  transform: rotate(180deg);
}

.advanced-content {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e5ea;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-label-small {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #1d1d1f;
  margin-bottom: 0.5rem;
}

.form-select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #e5e5ea;
  border-radius: 8px;
  font-size: 0.9375rem;
  color: #1d1d1f;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
}

.form-select:focus {
  outline: none;
  border-color: #2196f3;
  box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid #e5e5ea;
}

.btn-primary {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.9375rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
}

.btn-secondary {
  padding: 0.875rem 1.5rem;
  background: white;
  color: #6e6e73;
  border: 1px solid #e5e5ea;
  border-radius: 8px;
  font-size: 0.9375rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: #f8f9fa;
  border-color: #d1d1d6;
}

/* Sidebar */
.sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.panel {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.panel h2 {
  font-size: 1.125rem;
  font-weight: 600;
  margin: 0 0 1.5rem 0;
  color: #1d1d1f;
}

/* Tips Panel */
.tips-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.tip-item {
  display: flex;
  gap: 1rem;
}

.tip-number {
  width: 32px;
  height: 32px;
  flex-shrink: 0;
  background: #e3f2fd;
  color: #2196f3;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
}

.tip-content h3 {
  font-size: 0.9375rem;
  font-weight: 600;
  margin: 0 0 0.25rem 0;
  color: #1d1d1f;
}

.tip-content p {
  font-size: 0.8125rem;
  color: #6e6e73;
  margin: 0;
  line-height: 1.5;
}

/* Recent Generations */
.recent-list {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.recent-item {
  padding: 1rem 0;
  border-bottom: 1px solid #e5e5ea;
  cursor: pointer;
  transition: background 0.2s;
}

.recent-item:last-child {
  border-bottom: none;
}

.recent-item:hover {
  background: #f8f9fa;
  margin: 0 -1.5rem;
  padding-left: 1.5rem;
  padding-right: 1.5rem;
}

.recent-title {
  font-size: 0.9375rem;
  font-weight: 500;
  color: #1d1d1f;
  margin-bottom: 0.25rem;
}

.recent-time {
  font-size: 0.8125rem;
  color: #6e6e73;
}

/* Generated Content Panel */
.generated-content-panel {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #e5e5ea;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.preview-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  margin: 0;
  color: #1d1d1f;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #6e6e73;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s;
}

.btn-close:hover {
  background: #f0f0f2;
  color: #1d1d1f;
}

.preview-meta {
  display: flex;
  gap: 2rem;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  flex-wrap: wrap;
}

.meta-item {
  font-size: 0.875rem;
  color: #6e6e73;
}

.meta-item strong {
  color: #1d1d1f;
  margin-right: 0.5rem;
}

.preview-content {
  padding: 1.5rem;
  background: #f8f9fa;
  border-radius: 8px;
  line-height: 1.6;
  color: #1d1d1f;
  max-height: 600px;
  overflow-y: auto;
}

.preview-content h1,
.preview-content h2,
.preview-content h3 {
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
  color: #1d1d1f;
}

.preview-content h1 {
  font-size: 1.75rem;
}

.preview-content h2 {
  font-size: 1.5rem;
}

.preview-content h3 {
  font-size: 1.25rem;
}

.preview-content p {
  margin-bottom: 1rem;
}

.preview-content ul,
.preview-content ol {
  margin-bottom: 1rem;
  padding-left: 2rem;
}

.preview-content li {
  margin-bottom: 0.5rem;
}

/* Messages */
.message {
  margin-top: 1.5rem;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  animation: slideIn 0.3s ease-out;
}

.message.success {
  background: #e8f5e9;
  color: #2e7d32;
  border: 1px solid #4caf50;
}

.message.error {
  background: #ffebee;
  color: #c62828;
  border: 1px solid #f44336;
}

.message-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: inherit;
  cursor: pointer;
  padding: 0;
  margin-left: 1rem;
  opacity: 0.7;
  transition: opacity 0.2s;
}

.message-close:hover {
  opacity: 1;
}

/* Spinner */
.spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Button disabled state */
.btn-primary:disabled,
.btn-secondary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.btn-primary:disabled:hover {
  transform: none;
  box-shadow: none;
}

/* Responsive */
@media (max-width: 768px) {
  .preview-meta {
    flex-direction: column;
    gap: 0.75rem;
  }

  .preview-content {
    max-height: 400px;
  }
}
</style>
