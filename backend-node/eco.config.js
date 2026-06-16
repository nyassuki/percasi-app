module.exports = {
  apps: [
    {
      name: "percasi-backend",
      script: "./server.js",
      exec_mode: "cluster",
      instances: "max",
      watch: false,
      env: {
        NODE_ENV: ".env"
      },
      env_production: {
        NODE_ENV: ".env"
      }
    }
  ]
};
