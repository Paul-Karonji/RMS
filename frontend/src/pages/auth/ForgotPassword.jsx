import { useState } from 'react';
import authService from '../../services/authService';
import { ForgotPasswordForm } from '../../components/auth';
import { Card } from '../../components/common';
import { BuildingOffice2Icon } from '@heroicons/react/24/outline';

const ForgotPassword = () => {
  const [loading, setLoading] = useState(false);

  const handleForgotPassword = async (data) => {
    setLoading(true);
    try {
      await authService.forgotPassword(data.email);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-bg flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <div className="inline-flex items-center justify-center w-16 h-16 bg-primary rounded-xl mb-4">
            <BuildingOffice2Icon className="h-8 w-8 text-white" />
          </div>
          <h1 className="text-2xl font-bold text-text">Forgot Password</h1>
          <p className="text-muted mt-2">We'll help you reset it</p>
        </div>

        <Card padding="lg">
          <ForgotPasswordForm onSubmit={handleForgotPassword} loading={loading} />
        </Card>
      </div>
    </div>
  );
};

export default ForgotPassword;
