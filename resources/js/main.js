import { createApp } from 'vue'

import DashboardApp from "./DashboardApp.vue";
import GenerateContentApp from "./GenerateContentApp.vue";
import ContentSettingsApp from "./ContentSettingsApp.vue"
import EmptyApp from "./404App.vue"

function mountApp(component, selector) {
    const el = document.querySelector(selector)
    if (el) {
        const app = createApp(component)
        app.mount(selector)
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('fluent-content-dashboard')) {
        mountApp(DashboardApp, '#fluent-content-dashboard')

    }else if(document.getElementById('fluent-generate-content')){
        mountApp(GenerateContentApp, '#fluent-generate-content')

    }else if(document.getElementById('fluent-content-settings')){
        mountApp(ContentSettingsApp, '#fluent-content-settings')

    } else if(document.getElementById('fluent-content-404')){
        mountApp(EmptyApp, '#fluent-content-404')
    }
    else {
        mountApp(DashboardApp, '#fluent-content-dashboard')
    }
})

