import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import { Card, LoadingSpinner } from '../../components/common';
import {
  BuildingOfficeIcon,
  HomeModernIcon,
  UserGroupIcon,
  CurrencyDollarIcon,
  PlusIcon,
  ClipboardDocumentListIcon,
} from '@heroicons/react/24/outline';

const CompanyDashboard = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);

  const stats = [
    {
      name: 'Total Properties',
      value: '0',
      icon: BuildingOfficeIcon,
      color: 'bg-blue-500',
      href: '/company/properties',
    },
    {
      name: 'Total Units',
      value: '0',
      icon: HomeModernIcon,
      color: 'bg-green-500',
      href: '/company/units',
    },
    {
      name: 'Active Tenants',
      value: '0',
      icon: UserGroupIcon,
      color: 'bg-purple-500',
      href: '/company/tenants',
    },
    {
      name: 'Monthly Revenue',
      value: 'KES 0',
      icon: CurrencyDollarIcon,
      color: 'bg-amber-500',
      href: '/company/payments',
    },
  ];

  const quickActions = [
    {
      title: 'Add New Property',
      description: 'Register a new property to manage',
      icon: BuildingOfficeIcon,
      href: '/company/properties/create',
      color: 'text-blue-600 bg-blue-50',
    },
    {
      title: 'View Properties',
      description: 'Manage your property portfolio',
      icon: ClipboardDocumentListIcon,
      href: '/company/properties',
      color: 'text-green-600 bg-green-50',
    },
    {
      title: 'Record Payment',
      description: 'Record a tenant payment',
      icon: CurrencyDollarIcon,
      href: '/company/payments',
      color: 'text-amber-600 bg-amber-50',
    },
  ];

  return (
    <div className="space-y-6">
      {/* Welcome Section */}
      <div>
        <h1 className="text-2xl font-bold text-text">
          Welcome back, {user?.name?.split(' ')[0] || 'User'}!
        </h1>
        <p className="text-muted mt-1">
          Here's what's happening with your properties today.
        </p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {stats.map((stat) => (
          <Card
            key={stat.name}
            padding="md"
            className="cursor-pointer hover:shadow-lg transition-shadow"
            onClick={() => navigate(stat.href)}
          >
            <div className="flex items-center gap-4">
              <div className={`${stat.color} p-3 rounded-lg`}>
                <stat.icon className="h-6 w-6 text-white" />
              </div>
              <div>
                <p className="text-sm text-muted">{stat.name}</p>
                <p className="text-2xl font-bold text-text">{stat.value}</p>
              </div>
            </div>
          </Card>
        ))}
      </div>

      {/* Quick Actions & Recent Activity */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card padding="md">
          <h2 className="text-lg font-semibold text-text mb-4">Quick Actions</h2>
          <div className="space-y-3">
            {quickActions.map((action) => (
              <button
                key={action.title}
                onClick={() => navigate(action.href)}
                className="w-full flex items-center gap-4 px-4 py-3 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors text-left"
              >
                <div className={`p-2 rounded-lg ${action.color}`}>
                  <action.icon className="w-5 h-5" />
                </div>
                <div>
                  <p className="font-medium text-text">{action.title}</p>
                  <p className="text-sm text-muted">{action.description}</p>
                </div>
              </button>
            ))}
          </div>
        </Card>

        <Card padding="md">
          <h2 className="text-lg font-semibold text-text mb-4">Recent Activity</h2>
          <div className="text-center py-8">
            <div className="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
              <ClipboardDocumentListIcon className="w-6 h-6 text-muted" />
            </div>
            <p className="text-muted">No recent activity</p>
            <p className="text-sm text-muted mt-1">
              Your recent actions will appear here
            </p>
          </div>
        </Card>
      </div>

      {/* Pending Approvals (for admins) */}
      {user?.role === 'company_admin' && (
        <Card padding="md">
          <h2 className="text-lg font-semibold text-text mb-4">Pending Approvals</h2>
          <div className="text-center py-8">
            <p className="text-muted">No pending approvals</p>
            <p className="text-sm text-muted mt-1">
              Properties awaiting your review will appear here
            </p>
          </div>
        </Card>
      )}
    </div>
  );
};

export default CompanyDashboard;
