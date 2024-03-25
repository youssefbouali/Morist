import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react-swc'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    host: '0.0.0.0', // or '0.0.0.0' to listen on all network interfaces
    port: 9400, // specify your desired port
  },
})
