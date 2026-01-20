# RMS Deployment Instructions - Quick Guide

## Step 1: Deploy Backend to Render.com (15 minutes)

### A. Create New Web Service
1. Go to https://render.com/dashboard
2. Click **"New +"** button → Select **"Web Service"**
3. Connect your GitHub repository: **Paul-Karonji/RMS**
4. Click **"Connect"**

### B. Configure Service
**Basic Settings:**
- **Name**: `rms-backend` (or any name you prefer)
- **Region**: Choose closest to you
- **Branch**: `main`
- **Root Directory**: `backend`
- **Runtime**: Select **"Node"** or **"Docker"** (either works)

**Build & Deploy:**
- **Build Command**:
  ```
  composer install --optimize-autoloader --no-dev
  ```
- **Start Command**:
  ```
  php artisan serve --host=0.0.0.0 --port=$PORT
  ```

**Instance Type:**
- Select **"Free"** (this gives you 750 hours/month FREE)

### C. Add Environment Variables
Click **"Environment"** tab and add ALL variables from `.env.production` file.

**IMPORTANT:** Replace these placeholders:
- `YOUR-APP-NAME` → Replace with your actual Render app name (e.g., `rms-backend`)
- `YOUR-FRONTEND-URL` → Leave as is for now, we'll update this after deploying frontend

**Quick way to add variables:**
1. Open `.env.production` file
2. Copy all content
3. In Render, click "Add from .env"
4. Paste the content
5. Click "Add"

### D. Deploy
1. Click **"Create Web Service"**
2. Wait 5-10 minutes for deployment
3. You'll get a URL like: `https://rms-backend.onrender.com`

### E. Run Database Migrations
After deployment completes:
1. In Render dashboard, go to your service
2. Click **"Shell"** tab
3. Run:
   ```bash
   php artisan migrate --force
   ```

---

## Step 2: Deploy Frontend to Vercel (10 minutes)

### A. Import Project
1. Go to https://vercel.com/dashboard
2. Click **"Add New..."** → **"Project"**
3. Find your **RMS** repository
4. Click **"Import"**

### B. Configure Project
**Framework Preset:** Vite  
**Root Directory:** `frontend`  
**Build Command:** `npm run build`  
**Output Directory:** `dist`

### C. Add Environment Variables
Click **"Environment Variables"** and add:

```
VITE_API_URL=https://YOUR-BACKEND-URL.onrender.com/api
VITE_APP_NAME=RentalSaaS
VITE_STRIPE_PUBLISHABLE_KEY=pk_test_51SH7auEg2HmtU5bVZ3ZzJA16enrM9XfLQQd9bHMtcbfqMEopM6EUY96przUAnQetfezZ
```

**Replace:**
- `YOUR-BACKEND-URL` → Your actual Render URL (e.g., `rms-backend`)

### D. Deploy
1. Click **"Deploy"**
2. Wait 2-3 minutes
3. You'll get a URL like: `https://rms-xxx.vercel.app`

---

## Step 3: Update Backend CORS (5 minutes)

Now that you have your frontend URL, update the backend:

1. Go back to **Render.com** → Your backend service
2. Click **"Environment"** tab
3. Find `SANCTUM_STATEFUL_DOMAINS`
4. Update value to: `your-actual-frontend.vercel.app` (without https://)
5. Click **"Save Changes"**
6. Service will automatically redeploy (takes 2-3 minutes)

---

## Step 4: Test Your Deployment (5 minutes)

### Test Backend
Open: `https://your-backend.onrender.com/api/health`

Should see:
```json
{
  "status": "healthy",
  "database": "connected"
}
```

### Test Frontend
1. Open: `https://your-frontend.vercel.app`
2. Try to register a new account
3. Login
4. Check dashboard

---

## Troubleshooting

### Backend won't start
- Check logs in Render dashboard
- Verify all environment variables are set
- Make sure `DB_SSLMODE=require` is set

### Frontend can't connect to backend
- Check `VITE_API_URL` is correct in Vercel
- Check `SANCTUM_STATEFUL_DOMAINS` includes your Vercel domain in Render
- Redeploy both services after changes

### First request is slow (30-60 seconds)
- This is normal for Render free tier
- It "sleeps" after 15 minutes of inactivity
- Subsequent requests are fast
- Solution: Use UptimeRobot.com (free) to ping every 14 minutes

---

## Success Checklist

- [ ] Backend deployed to Render
- [ ] Frontend deployed to Vercel
- [ ] Database migrations run
- [ ] Can register new account
- [ ] Can login
- [ ] Dashboard loads
- [ ] No console errors

---

## Your URLs

After deployment, save these:

- **Backend API**: `https://__________.onrender.com`
- **Frontend App**: `https://__________.vercel.app`
- **Database**: Supabase (already configured)
- **Storage**: Cloudflare R2 (already configured)

---

## Total Cost: $0/month 🎉

**What's FREE:**
- Render.com: 750 hours/month
- Vercel: Unlimited deployments
- Supabase: 500MB database
- Cloudflare R2: 10GB storage

**When to upgrade:**
- Render ($7/month): For 24/7 uptime (no sleep)
- Supabase ($25/month): When database > 500MB

---

**Need Help?**
- Check the detailed `deployment_guide.md` for more information
- Review logs in Render/Vercel dashboards
- Verify environment variables are correct
