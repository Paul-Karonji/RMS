import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

import { AuthProvider } from './contexts/AuthContext';
import ProtectedRoute from './routes/ProtectedRoute';

import Login from './pages/auth/Login';
import Register from './pages/auth/Register';
import ForgotPassword from './pages/auth/ForgotPassword';
import ResetPassword from './pages/auth/ResetPassword';
import Dashboard from './pages/dashboard/Dashboard';

// Platform Owner Pages
import PlatformLogin from './pages/platform/PlatformLogin';
import PlatformLayout from './components/platform/PlatformLayout';
import PlatformDashboard from './pages/platform/Dashboard';
import Companies from './pages/platform/Companies';
import CreateCompany from './pages/platform/CreateCompany';
import Revenue from './pages/platform/Revenue';

// Company Pages
import CompanyLayout from './components/layout/CompanyLayout';
import {
  Properties,
  PropertyDetails,
  CreateProperty,
  EditProperty,
  CreateUnit,
  EditUnit,
  CompanyDashboard,
} from './pages/properties';

function App() {
  return (
    <AuthProvider>
      <Router>
        <Routes>
          {/* Public Routes */}
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route path="/forgot-password" element={<ForgotPassword />} />
          <Route path="/reset-password" element={<ResetPassword />} />

          {/* Platform Owner Routes */}
          <Route path="/platform/login" element={<PlatformLogin />} />
          <Route path="/platform" element={<PlatformLayout />}>
            <Route path="dashboard" element={<PlatformDashboard />} />
            <Route path="companies" element={<Companies />} />
            <Route path="companies/create" element={<CreateCompany />} />
            <Route path="revenue" element={<Revenue />} />
          </Route>

          {/* Protected Routes */}
          <Route element={<ProtectedRoute />}>
            <Route path="/dashboard" element={<Dashboard />} />
          </Route>

          {/* Company Routes (Protected) */}
          <Route element={<ProtectedRoute />}>
            <Route path="/company" element={<CompanyLayout />}>
              <Route index element={<Navigate to="/company/dashboard" replace />} />
              <Route path="dashboard" element={<CompanyDashboard />} />
              <Route path="properties" element={<Properties />} />
              <Route path="properties/create" element={<CreateProperty />} />
              <Route path="properties/:id" element={<PropertyDetails />} />
              <Route path="properties/:id/edit" element={<EditProperty />} />
              <Route path="properties/:id/units/create" element={<CreateUnit />} />
              <Route path="units/:id/edit" element={<EditUnit />} />
            </Route>
          </Route>

          {/* Default redirect */}
          <Route path="/" element={<Navigate to="/login" replace />} />
          <Route path="*" element={<Navigate to="/login" replace />} />
        </Routes>
      </Router>
      <ToastContainer
        position="top-right"
        autoClose={5000}
        hideProgressBar={false}
        newestOnTop
        closeOnClick
        rtl={false}
        pauseOnFocusLoss
        draggable
        pauseOnHover
        theme="light"
      />
    </AuthProvider>
  );
}

export default App
