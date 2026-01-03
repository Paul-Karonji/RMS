import { useState } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import authService from '../../services/authService';
import { ResetPasswordForm } from '../../components/auth';
import { Card, Alert } from '../../components/common';
import { BuildingOffice2Icon } from '@heroicons/react/24/outline';

const ResetPassword = () => {
  const [loading, setLoading] = useState(false);
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();

  const token = searchParams.get('token');
  const email = searchParams.get('email');

  const handleResetPassword = async (data) => {
    setLoading(true);
    try {
      await authService.resetPassword({
        token,
        email,
        password: data.password,
        passwordConfirmation: data.passwordConfirmation,
      });
      setTimeout(() => {
        navigate('/login');
      }, 2000);
    } finally {
      setLoading(false);
    }
  };

  if (!token || !email) {
    return (
      <div className="min-h-screen bg-bg flex items-center justify-center p-4">
        <div className="w-full max-w-md">
          <Card padding="lg">
            <Alert
              type="error"
              message="Invalid password reset link. Please request a new one."
            />
          </Card>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-bg flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <div className="inline-flex items-center justify-center w-16 h-16 bg-primary rounded-xl mb-4">
            <BuildingOffice2Icon className="h-8 w-8 text-white" />
          </div>
          <h1 className="text-2xl font-bold text-text">Reset Password</h1>
          <p className="text-muted mt-2">Enter your new password</p>
        </div>

        <Card padding="lg">
          <ResetPasswordForm onSubmit={handleResetPassword} loading={loading} />
        </Card>
      </div>
    </div>
  );
};

export default ResetPassword;
