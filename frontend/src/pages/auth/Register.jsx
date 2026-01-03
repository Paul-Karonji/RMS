import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import { RegisterForm } from '../../components/auth';
import { Card } from '../../components/common';
import { BuildingOffice2Icon } from '@heroicons/react/24/outline';

const Register = () => {
  const [loading, setLoading] = useState(false);
  const { register: registerUser } = useAuth();
  const navigate = useNavigate();

  const handleRegister = async (data) => {
    setLoading(true);
    try {
      await registerUser(data);
      navigate('/dashboard');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-bg flex items-center justify-center p-4">
      <div className="w-full max-w-2xl">
        <div className="text-center mb-8">
          <div className="inline-flex items-center justify-center w-16 h-16 bg-primary rounded-xl mb-4">
            <BuildingOffice2Icon className="h-8 w-8 text-white" />
          </div>
          <h1 className="text-2xl font-bold text-text">Create Account</h1>
          <p className="text-muted mt-2">Start managing your properties today</p>
        </div>

        <Card padding="lg">
          <RegisterForm onSubmit={handleRegister} loading={loading} />
        </Card>
      </div>
    </div>
  );
};

export default Register;
