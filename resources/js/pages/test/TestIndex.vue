<!--
Test & Demo Navigation Index

Central hub for accessing all test and demo pages during development.
Provides easy navigation to component demos, feature tests, and integration tests.

Usage: Navigate to /test to see all available test pages
-->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// ========================================================================
// Test Pages Configuration
// ========================================================================

interface TestPage {
    title: string;
    description: string;
    route: string;
    status: 'completed' | 'in-progress' | 'planned';
    sprint: string;
    userStory?: string;
}

const testPages = computed<TestPage[]>(() => [
    {
        title: 'Geolocation Test',
        description: 'Test GPS tracking, speed monitoring, and location permissions. Displays real-time coordinates, speed, accuracy, and heading.',
        route: '/test/geolocation',
        status: 'completed',
        sprint: 'Sprint 3',
        userStory: 'US-3.1',
    },
    {
        title: 'SpeedGauge Demo',
        description: 'Interactive demo of the circular speedometer gauge. Test speed zones, color transitions, responsive sizing, and accessibility features.',
        route: '/test/speed-gauge-demo',
        status: 'completed',
        sprint: 'Sprint 3',
        userStory: 'US-3.3',
    },
    {
        title: 'TripControls Demo',
        description: 'Full integration test of trip controls with GPS tracking. Start/stop trips, test duration display, speed logging, and error handling.',
        route: '/test/trip-controls-demo',
        status: 'completed',
        sprint: 'Sprint 3',
        userStory: 'US-3.4',
    },
    {
        title: 'TripStats Demo',
        description: 'Real-time trip statistics display with mock data controls. Test all 6 metrics, formatting, responsive design, and integration with trip store.',
        route: '/test/trip-stats-demo',
        status: 'completed',
        sprint: 'Sprint 3',
        userStory: 'US-3.5',
    },
]);

/**
 * Get status badge color based on completion status.
 */
function getStatusColor(status: TestPage['status']): string {
    switch (status) {
        case 'completed':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'in-progress':
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'planned':
            return 'bg-gray-100 text-gray-800 border-gray-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
}

/**
 * Get status icon based on completion status.
 */
function getStatusIcon(status: TestPage['status']): string {
    switch (status) {
        case 'completed':
            return '✅';
        case 'in-progress':
            return '🚧';
        case 'planned':
            return '📋';
        default:
            return '❓';
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-3xl font-bold text-gray-900">
                        🧪 Test & Demo Pages
                    </h1>
                    <Link
                        href="/"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        ← Back to Home
                    </Link>
                </div>
                <p class="text-gray-600">
                    Development testing pages for Speed Tracker components and features.
                    Click any card below to access the demo page.
                </p>
            </div>

            <!-- Info Banner -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <svg
                        class="h-5 w-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-blue-900 mb-1">
                            Development Environment
                        </h3>
                        <p class="text-sm text-blue-800">
                            These pages are for development and testing only. They will not be
                            accessible in production. Some tests may require GPS/location access.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Test Pages Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Link
                    v-for="page in testPages"
                    :key="page.route"
                    :href="page.route"
                    class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden border border-gray-200 hover:border-blue-300"
                >
                    <!-- Card Header -->
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-start justify-between mb-2">
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ page.title }}
                            </h2>
                            <span
                                :class="[
                                    'px-2 py-1 rounded text-xs font-medium border',
                                    getStatusColor(page.status)
                                ]"
                            >
                                {{ getStatusIcon(page.status) }} {{ page.status }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                            <span class="font-medium">{{ page.sprint }}</span>
                            <span v-if="page.userStory">•</span>
                            <span v-if="page.userStory">{{ page.userStory }}</span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6">
                        <p class="text-sm text-gray-600 mb-4">
                            {{ page.description }}
                        </p>
                        <div class="flex items-center text-blue-600 text-sm font-medium">
                            Open Demo
                            <svg
                                class="ml-2 h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                                />
                            </svg>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Statistics -->
            <div class="mt-8 bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Sprint 3 Progress
                </h3>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-3xl font-bold text-green-600">
                            {{ testPages.filter(p => p.status === 'completed').length }}
                        </p>
                        <p class="text-sm text-gray-600">Completed</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-yellow-600">
                            {{ testPages.filter(p => p.status === 'in-progress').length }}
                        </p>
                        <p class="text-sm text-gray-600">In Progress</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-400">
                            {{ testPages.filter(p => p.status === 'planned').length }}
                        </p>
                        <p class="text-sm text-gray-600">Planned</p>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="mt-8 bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    📅 Coming Next
                </h3>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start">
                        <span class="text-yellow-600 mr-2">→</span>
                        <span><strong>US-3.6:</strong> Speedometer Page Integration - Combine SpeedGauge, TripControls, and TripStats into full employee page</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-gray-400 mr-2">→</span>
                        <span><strong>US-3.7:</strong> Speed Limit Violation Alerts - Browser notifications, audio alerts, visual indicators</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-gray-400 mr-2">→</span>
                        <span><strong>US-4.1:</strong> My Trips List Page - View past trips with filters and pagination</span>
                    </li>
                </ul>
            </div>

            <!-- Quick Links -->
            <div class="mt-8 text-center text-sm text-gray-600">
                <p class="mb-2">Quick Links:</p>
                <div class="flex justify-center space-x-4">
                    <a href="/docs/SCRUM_WORKFLOW.md" class="text-blue-600 hover:text-blue-700 underline">
                        Scrum Workflow
                    </a>
                    <a href="/docs/ARCHITECTURE.md" class="text-blue-600 hover:text-blue-700 underline">
                        Architecture Docs
                    </a>
                    <a href="https://github.com" class="text-blue-600 hover:text-blue-700 underline">
                        GitHub Repository
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
