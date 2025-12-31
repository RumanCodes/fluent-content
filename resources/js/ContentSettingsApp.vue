<template>
  <div class="settings-page">
    <!-- Header -->
    <div class="settings-header">
      <div>
        <h1>Settings</h1>
        <p class="subtitle">Configure WizWithAI plugin settings and preferences.</p>
      </div>
      <button class="btn-save" @click="saveSettings" :disabled="saving">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
          <polyline points="17 21 17 13 7 13 7 21"/>
          <polyline points="7 3 7 8 15 8"/>
        </svg>
        {{ saving ? 'Saving...' : 'Save Changes' }}
      </button>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="message.text" :class="['message', message.type]">
      {{ message.text }}
      <button class="message-close" @click="message.text = ''">×</button>
    </div>

    <!-- API Configuration Section -->
    <div class="settings-section">
      <div class="section-header">
        <div class="section-icon api-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="16 18 22 12 16 6"/>
            <polyline points="8 6 2 12 8 18"/>
          </svg>
        </div>
        <div>
          <h2>API Configuration</h2>
          <p class="section-description">Connect your AI service API for content generation</p>
        </div>
      </div>

      <div class="section-content">
        <div class="form-row">
          <label class="form-label">AI Provider</label>
          <select v-model="settings.apiProvider" class="form-select full-width">
            <option value="openai-gpt4">OpenAI (GPT-4)</option>
            <option value="anthropic">Anthropic Claude</option>
            <option value="google">Google AI</option>
          </select>
          <p class="form-hint" v-if="settings.apiProvider !== 'openai-gpt4'">
            Note: Only OpenAI is currently supported. Other providers coming soon.
          </p>
        </div>

        <div class="form-row">
          <label class="form-label">
            API Key <span class="required">*</span>
          </label>
          <div class="api-key-input-wrapper">
            <input
                v-model="settings.apiKey"
                :type="showApiKey ? 'text' : 'password'"
                class="form-input full-width"
                placeholder="Enter your API key (e.g., sk-...)"
                @input="apiKeyChanged = true"
            />
            <button
                type="button"
                class="toggle-visibility"
                @click="showApiKey = !showApiKey"
                :title="showApiKey ? 'Hide API key' : 'Show API key'"
            >
              <svg v-if="!showApiKey" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
          <p class="form-hint">
            Your API key is encrypted and stored securely.
            <a href="https://platform.openai.com/api-keys" target="_blank">Get your OpenAI API key</a>
          </p>
          <p class="form-hint status" v-if="settings.hasApiKey && !apiKeyChanged">
            <span class="status-indicator success">✓</span> API key configured
          </p>
        </div>

        <div class="form-row">
          <label class="checkbox-label">
            <input type="checkbox" v-model="settings.testConnection" />
            <span>Test connection after saving</span>
          </label>
        </div>

        <!-- Connection Test Button -->
        <div class="form-row" v-if="settings.hasApiKey">
          <button
              type="button"
              class="btn-secondary"
              @click="testConnection"
              :disabled="testing"
          >
            {{ testing ? 'Testing...' : 'Test Connection Now' }}
          </button>
        </div>

        <!-- Connection Test Result -->
        <div v-if="connectionTest" :class="['connection-result', connectionTest.success ? 'success' : 'error']">
          <strong>{{ connectionTest.success ? 'Success!' : 'Failed' }}</strong>
          <p>{{ connectionTest.message }}</p>
          <p v-if="connectionTest.model" class="model-info">Model: {{ connectionTest.model }}</p>
        </div>
      </div>
    </div>

    <!-- Content Generation Settings Section -->
    <div class="settings-section">
      <div class="section-header">
        <div class="section-icon content-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
            <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
          </svg>
        </div>
        <div>
          <h2>Content Generation Settings</h2>
          <p class="section-description">Default settings for AI content generation</p>
        </div>
      </div>

      <div class="section-content">
        <div class="form-grid">
          <div class="form-column">
            <label class="form-label">Default Word Count</label>
            <select v-model="settings.wordCount" class="form-select">
              <option value="500">500 words</option>
              <option value="800">800 words</option>
              <option value="1000">1000 words</option>
              <option value="1500">1500 words</option>
            </select>
          </div>

          <div class="form-column">
            <label class="form-label">Default Tone</label>
            <select v-model="settings.tone" class="form-select">
              <option value="professional">Professional</option>
              <option value="casual">Casual</option>
              <option value="friendly">Friendly</option>
              <option value="formal">Formal</option>
            </select>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-column">
            <label class="form-label">Default Language</label>
            <select v-model="settings.language" class="form-select">
              <option value="english">English</option>
              <option value="spanish">Spanish</option>
              <option value="french">French</option>
              <option value="german">German</option>
            </select>
          </div>

          <div class="form-column">
            <label class="form-label">Default Creativity</label>
            <select v-model="settings.creativity" class="form-select">
              <option value="low">Low (More Focused)</option>
              <option value="medium">Medium (Balanced)</option>
              <option value="high">High (More Creative)</option>
            </select>
          </div>
        </div>

        <div class="checkbox-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="settings.autoGenerateTitle" />
            <span>Auto-generate titles if not provided</span>
          </label>
          <label class="checkbox-label">
            <input type="checkbox" v-model="settings.autoGenerateMeta" />
            <span>Auto-generate meta descriptions</span>
          </label>
          <label class="checkbox-label">
            <input type="checkbox" v-model="settings.autoAddImages" />
            <span>Auto-add featured images (coming soon)</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Publishing Settings Section -->
    <div class="settings-section">
      <div class="section-header">
        <div class="section-icon publish-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
        </div>
        <div>
          <h2>Publishing Settings</h2>
          <p class="section-description">Configure how content is published to your WordPress site</p>
        </div>
      </div>

      <div class="section-content">
        <div class="form-row">
          <label class="form-label">Default Category</label>
          <select v-model="settings.defaultCategory" class="form-select full-width">
            <option value="">Select category</option>
            <option value="uncategorized">Uncategorized</option>
            <option value="blog">Blog</option>
            <option value="news">News</option>
          </select>
        </div>

        <div class="form-row">
          <label class="form-label">Default Author</label>
          <select v-model="settings.defaultAuthor" class="form-select full-width">
            <option value="admin">Admin</option>
            <option value="editor">Editor</option>
          </select>
        </div>

        <div class="form-row">
          <label class="form-label">Default Post Status</label>
          <select v-model="settings.postStatus" class="form-select full-width">
            <option value="draft">Save as Draft</option>
            <option value="pending">Pending Review</option>
            <option value="publish">Publish Immediately</option>
          </select>
        </div>

        <div class="checkbox-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="settings.allowComments" />
            <span>Allow comments by default</span>
          </label>
          <label class="checkbox-label">
            <input type="checkbox" v-model="settings.sendEmailNotification" />
            <span>Send email notification when post is published</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Scheduling Settings Section -->
    <div class="settings-section">
      <div class="section-header">
        <div class="section-icon schedule-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        </div>
        <div>
          <h2>Scheduling Settings</h2>
          <p class="section-description">Configure automatic post scheduling behavior</p>
        </div>
      </div>

      <div class="section-content">
        <div class="form-row">
          <label class="form-label">Timezone</label>
          <select v-model="settings.timezone" class="form-select full-width">
            <option value="utc">UTC</option>
            <option value="est">Eastern Time (EST)</option>
            <option value="pst">Pacific Time (PST)</option>
            <option value="gmt">GMT</option>
          </select>
        </div>

        <div class="form-grid">
          <div class="form-column">
            <label class="form-label">Retry Attempts on Failure</label>
            <input
                v-model.number="settings.retryAttempts"
                type="number"
                class="form-input"
                min="1"
                max="10"
            />
          </div>

          <div class="form-column">
            <label class="form-label">Retry Interval (minutes)</label>
            <input
                v-model.number="settings.retryInterval"
                type="number"
                class="form-input"
                min="5"
                max="60"
            />
          </div>
        </div>

        <div class="form-row">
          <label class="checkbox-label">
            <input type="checkbox" v-model="settings.removeAfterSuccess" />
            <span>Remove from schedule after successful publish</span>
          </label>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "SettingsPage",
  data() {
    return {
      settings: {
        apiProvider: 'openai-gpt4',
        apiKey: '',
        hasApiKey: false,
        testConnection: false,
        wordCount: '1000',
        tone: 'professional',
        language: 'english',
        creativity: 'medium',
        autoGenerateTitle: true,
        autoGenerateMeta: true,
        autoAddImages: false,
        defaultCategory: '',
        defaultAuthor: 'admin',
        postStatus: 'draft',
        allowComments: true,
        sendEmailNotification: true,
        timezone: 'utc',
        retryAttempts: 3,
        retryInterval: 15,
        removeAfterSuccess: false
      },
      saving: false,
      testing: false,
      showApiKey: false,
      apiKeyChanged: false,
      connectionTest: null,
      message: {
        text: '',
        type: ''
      }
    };
  },
  mounted() {
    this.loadSettings();
  },
  methods: {
    async loadSettings() {
      try {
        const response = await fetch(
            `${fluentContentObject.ajaxurl}?action=fluent_content_get_settings`,
            {
              method: 'GET',
              headers: {
                'Content-Type': 'application/json'
              }
            }
        );

        const result = await response.json();

        if (result.success) {
          this.settings = { ...this.settings, ...result.data };
          this.apiKeyChanged = false;
        } else {
          this.showMessage('Failed to load settings', 'error');
        }
      } catch (error) {
        console.error('Error loading settings:', error);
        this.showMessage('Error loading settings: ' + error.message, 'error');
      }
    },

    async saveSettings() {
      if (!this.validateSettings()) {
        return;
      }

      this.saving = true;
      this.connectionTest = null;

      try {
        const response = await fetch(
            `${fluentContentObject.ajaxurl}?action=fluent_content_save_settings`,
            {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                nonce: fluentContentObject.nonce,
                settings: this.settings
              })
            }
        );

        const result = await response.json();

        if (result.success) {
          this.showMessage('Settings saved successfully!', 'success');
          this.apiKeyChanged = false;

          if (result.data.connectionTest) {
            this.connectionTest = result.data.connectionTest;
          }

          // Reload settings to get masked API key
          await this.loadSettings();
        } else {
          this.showMessage(result.data.message || 'Failed to save settings', 'error');
        }
      } catch (error) {
        console.error('Error saving settings:', error);
        this.showMessage('Error saving settings: ' + error.message, 'error');
      } finally {
        this.saving = false;
      }
    },

    async testConnection() {
      this.testing = true;
      this.connectionTest = null;

      try {
        const response = await fetch(
            `${fluentContentObject.ajaxurl}?action=fluent_content_test_connection`,
            {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                nonce: fluentContentObject.nonce
              })
            }
        );

        const result = await response.json();

        if (result.success) {
          this.connectionTest = result.data;
          this.showMessage('Connection test completed', 'success');
        } else {
          this.connectionTest = result.data;
          this.showMessage('Connection test failed', 'error');
        }
      } catch (error) {
        console.error('Error testing connection:', error);
        this.showMessage('Error testing connection: ' + error.message, 'error');
      } finally {
        this.testing = false;
      }
    },

    validateSettings() {
      if (!this.settings.apiKey && this.settings.apiProvider === 'openai-gpt4') {
        this.showMessage('Please enter your OpenAI API key', 'error');
        return false;
      }

      if (this.settings.retryAttempts < 1 || this.settings.retryAttempts > 10) {
        this.showMessage('Retry attempts must be between 1 and 10', 'error');
        return false;
      }

      if (this.settings.retryInterval < 5 || this.settings.retryInterval > 60) {
        this.showMessage('Retry interval must be between 5 and 60 minutes', 'error');
        return false;
      }

      return true;
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
.settings-page {
  max-width: 100%;
  padding: 2rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  background: #f5f5f7;
  min-height: 100vh;
  margin: -.7rem -1.3rem 0 -1.4rem;
}

/* Header */
.settings-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;

}

.settings-header h1 {
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

.btn-save {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #2196f3;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.9375rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-save:hover {
  background: #1976d2;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
}

/* Settings Section */
.settings-section {
  background: white;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.section-header {
  display: flex;
  gap: 1rem;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid #e5e5ea;
}

.section-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.api-icon {
  background: #e3f2fd;
  color: #2196f3;
}

.content-icon {
  background: #f3e5f5;
  color: #9c27b0;
}

.publish-icon {
  background: #e8f5e9;
  color: #4caf50;
}

.schedule-icon {
  background: #fff3e0;
  color: #ff9800;
}

.section-header h2 {
  font-size: 1.125rem;
  font-weight: 600;
  margin: 0 0 0.25rem 0;
  color: #1d1d1f;
}

.section-description {
  font-size: 0.875rem;
  color: #6e6e73;
  margin: 0;
}

.section-content {
  padding: 2rem;
}

/* Form Elements */
.form-row {
  margin-bottom: 1.5rem;
}

.form-row:last-child {
  margin-bottom: 0;
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #1d1d1f;
  margin-bottom: 0.5rem;
}

.required {
  color: #f44336;
}

.form-input,
.form-select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #e5e5ea;
  border-radius: 8px;
  font-size: 0.9375rem;
  color: #1d1d1f;
  background: white;
  transition: all 0.2s;
}

.form-input:focus,
.form-select:focus {
  outline: none;
  border-color: #2196f3;
  box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
}

.form-input::placeholder {
  color: #a1a1a6;
}

.full-width {
  width: 100%;
}

.form-hint {
  margin-top: 0.5rem;
  font-size: 0.8125rem;
  color: #6e6e73;
}

/* Form Grid */
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

.form-column {
  display: flex;
  flex-direction: column;
}

/* Checkbox */
.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9375rem;
  color: #1d1d1f;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #2196f3;
}

.checkbox-label:hover {
  color: #2196f3;
}

@media (max-width: 768px) {
  .settings-page {
    padding: 1rem;
  }

  .settings-header {
    flex-direction: column;
    gap: 1rem;
  }

  .btn-save {
    width: 100%;
    justify-content: center;
  }

  .section-content {
    padding: 1.5rem;
  }
}
</style>
