import { useState } from 'react';
import { Outlet, NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import {
  BuildingOfficeIcon,
  HomeModernIcon,
  UserGroupIcon,
  CurrencyDollarIcon,
  ChartBarIcon,
  WrenchScrewdriverIcon,
  Bars3Icon,
  XMarkIcon,
  ArrowRightOnRectangleIcon,
  UserCircleIcon,
} from '@heroicons/react/24/outline';

const navigation = [
  { name: 'Dashboard', href: '/company/dashboard', icon: ChartBarIcon },
  { name: 'Properties', href: '/company/properties', icon: BuildingOfficeIcon },
  { name: 'Units', href: '/company/units', icon: HomeModernIcon },
  { name: 'Tenants', href: '/company/tenants', icon: UserGroupIcon },
  { name: 'Payments', href: '/company/payments', icon: CurrencyDollarIcon },
  { name: 'Maintenance', href: '/company/maintenance', icon: WrenchScrewdriverIcon },
];

const CompanyLayout = () => {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <div className="min-h-screen bg-bg">
      {/* Mobile sidebar overlay */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      {/* Mobile sidebar */}
      <div
        className={`fixed inset-y-0 left-0 z-50 w-64 bg-surface shadow-lg transform transition-transform duration-300 ease-in-out lg:hidden ${
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        <div className="flex items-center justify-between h-16 px-4 border-b border-slate-200">
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
              <span className="text-white font-bold text-sm">RMS</span>
            </div>
            <span className="font-semibold text-text">RMS</span>
          </div>
          <button
            onClick={() => setSidebarOpen(false)}
            className="p-2 rounded-lg hover:bg-slate-100"
          >
            <XMarkIcon className="w-5 h-5 text-muted" />
          </button>
        </div>
        <nav className="p-4 space-y-1">
          {navigation.map((item) => (
            <NavLink
              key={item.name}
              to={item.href}
              onClick={() => setSidebarOpen(false)}
              className={({ isActive }) =>
                `flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors ${
                  isActive
                    ? 'bg-primary text-white'
                    : 'text-muted hover:bg-slate-100 hover:text-text'
                }`
              }
            >
              <item.icon className="w-5 h-5" />
              <span className="font-medium">{item.name}</span>
            </NavLink>
          ))}
        </nav>
      </div>

      {/* Desktop sidebar */}
      <div className="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-40 lg:w-64 lg:block">
        <div className="flex flex-col h-full bg-surface border-r border-slate-200">
          {/* Logo */}
          <div className="flex items-center gap-2 h-16 px-6 border-b border-slate-200">
            <div className="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
              <span className="text-white font-bold text-sm">RMS</span>
            </div>
            <span className="font-semibold text-text">Rental Management</span>
          </div>

          {/* Navigation */}
          <nav className="flex-1 p-4 space-y-1 overflow-y-auto">
            {navigation.map((item) => (
              <NavLink
                key={item.name}
                to={item.href}
                className={({ isActive }) =>
                  `flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors ${
                    isActive
                      ? 'bg-primary text-white'
                      : 'text-muted hover:bg-slate-100 hover:text-text'
                  }`
                }
              >
                <item.icon className="w-5 h-5" />
                <span className="font-medium">{item.name}</span>
              </NavLink>
            ))}
          </nav>

          {/* User section */}
          <div className="p-4 border-t border-slate-200">
            <div className="flex items-center gap-3 px-3 py-2">
              <UserCircleIcon className="w-8 h-8 text-muted" />
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-text truncate">
                  {user?.name || 'User'}
                </p>
                <p className="text-xs text-muted truncate">
                  {user?.role?.replace('_', ' ') || 'Role'}
                </p>
              </div>
            </div>
            <button
              onClick={handleLogout}
              className="flex items-center gap-3 w-full px-3 py-2.5 mt-2 text-muted hover:bg-slate-100 hover:text-text rounded-lg transition-colors"
            >
              <ArrowRightOnRectangleIcon className="w-5 h-5" />
              <span className="font-medium">Logout</span>
            </button>
          </div>
        </div>
      </div>

      {/* Main content */}
      <div className="lg:pl-64">
        {/* Top header */}
        <header className="sticky top-0 z-30 bg-surface border-b border-slate-200">
          <div className="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
            {/* Mobile menu button */}
            <button
              onClick={() => setSidebarOpen(true)}
              className="p-2 rounded-lg hover:bg-slate-100 lg:hidden"
            >
              <Bars3Icon className="w-6 h-6 text-muted" />
            </button>

            {/* Page title placeholder - can be customized per page */}
            <div className="hidden lg:block" />

            {/* Right side - notifications, user menu */}
            <div className="flex items-center gap-4">
              <div className="hidden sm:flex items-center gap-2">
                <UserCircleIcon className="w-8 h-8 text-muted" />
                <div className="text-right">
                  <p className="text-sm font-medium text-text">
                    {user?.name || 'User'}
                  </p>
                  <p className="text-xs text-muted capitalize">
                    {user?.role?.replace('_', ' ') || 'Role'}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </header>

        {/* Page content */}
        <main className="p-4 sm:p-6 lg:p-8">
          <Outlet />
        </main>
      </div>
    </div>
  );
};

export default CompanyLayout;
