import React from 'react';
import { Link, Outlet, useNavigate, useLocation } from 'react-router-dom';
import { platformService } from '../../services/platform';
import { toast } from 'react-toastify';

const PlatformLayout = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const platformUser = JSON.parse(localStorage.getItem('platform_user') || '{}');

  const handleLogout = async () => {
    try {
      await platformService.logout();
      localStorage.removeItem('platform_token');
      localStorage.removeItem('platform_user');
      toast.success('Logged out successfully');
      navigate('/platform/login');
    } catch (error) {
      console.error('Logout failed:', error);
      localStorage.removeItem('platform_token');
      localStorage.removeItem('platform_user');
      navigate('/platform/login');
    }
  };

  const isActive = (path) => {
    return location.pathname === path || location.pathname.startsWith(path + '/');
  };

  const navItems = [
    { path: '/platform/dashboard', label: 'Dashboard', icon: '📊' },
    { path: '/platform/companies', label: 'Companies', icon: '🏢' },
    { path: '/platform/revenue', label: 'Revenue', icon: '💰' },
  ];

  return (
    <div className="min-h-screen bg-gray-50">
      <nav className="bg-white shadow-sm border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16">
            <div className="flex">
              <div className="flex-shrink-0 flex items-center">
                <div className="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                  <span className="text-white text-lg font-bold">RMS</span>
                </div>
                <span className="ml-3 text-xl font-bold text-gray-900">Platform</span>
              </div>
              <div className="hidden sm:ml-8 sm:flex sm:space-x-4">
                {navItems.map((item) => (
                  <Link
                    key={item.path}
                    to={item.path}
                    className={`inline-flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                      isActive(item.path)
                        ? 'bg-blue-50 text-blue-700'
                        : 'text-gray-700 hover:bg-gray-50'
                    }`}
                  >
                    <span className="mr-2">{item.icon}</span>
                    {item.label}
                  </Link>
                ))}
              </div>
            </div>
            <div className="flex items-center">
              <div className="flex items-center space-x-4">
                <div className="text-sm">
                  <p className="font-medium text-gray-900">{platformUser.name}</p>
                  <p className="text-gray-500">{platformUser.role}</p>
                </div>
                <button
                  onClick={handleLogout}
                  className="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
                >
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <Outlet />
      </main>
    </div>
  );
};

export default PlatformLayout;
