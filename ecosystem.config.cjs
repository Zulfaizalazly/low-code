module.exports = {
    apps: [
        {
            name: 'arrahnumation-queue',
            script: 'artisan',
            args: 'queue:work --sleep=3 --tries=3 --max-time=3600',
            interpreter: 'php',
            instances: 1,
            autorestart: true,
            watch: false,
            max_memory_restart: '256M',
            env: {
                APP_ENV: 'production',
            },
        },
        {
            name: 'arrahnumation-scheduler',
            script: 'artisan',
            args: 'schedule:work',
            interpreter: 'php',
            instances: 1,
            autorestart: true,
            watch: false,
            max_memory_restart: '128M',
            env: {
                APP_ENV: 'production',
            },
        },
    ],
};
