module.exports = {
  apps: [
    {
      name: "percasi-frontend",
      script: "npm",
      args: "run dev -- --host 0.0.0.0", 
      watch: false,
      autorestart: true,
      env: {
        NODE_ENV: ".env"
      }
    }
  ]
};
