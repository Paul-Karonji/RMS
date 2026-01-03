import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Link } from 'react-router-dom';
import { resetPasswordSchema } from '../../utils/validators';
import { Button, Input, Alert } from '../common';

const ResetPasswordForm = ({ onSubmit, loading = false }) => {
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm({
    resolver: zodResolver(resetPasswordSchema),
    defaultValues: {
      password: '',
      passwordConfirmation: '',
    },
  });

  const handleFormSubmit = async (data) => {
    setError('');
    setSuccess('');
    try {
      await onSubmit(data);
      setSuccess('Password has been reset successfully. You can now sign in.');
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to reset password. Please try again.');
    }
  };

  return (
    <form onSubmit={handleSubmit(handleFormSubmit)} className="space-y-5">
      {error && (
        <Alert type="error" message={error} onClose={() => setError('')} />
      )}

      {success && (
        <Alert type="success" message={success} onClose={() => setSuccess('')} />
      )}

      <p className="text-sm text-muted">
        Enter your new password below.
      </p>

      <Input
        label="New Password"
        type="password"
        placeholder="Min. 8 characters"
        error={errors.password?.message}
        {...register('password')}
      />

      <Input
        label="Confirm New Password"
        type="password"
        placeholder="Confirm your new password"
        error={errors.passwordConfirmation?.message}
        {...register('passwordConfirmation')}
      />

      <Button type="submit" fullWidth loading={loading}>
        Reset Password
      </Button>

      <p className="text-center text-sm text-muted">
        <Link to="/login" className="text-primary font-semibold hover:underline">
          Back to Sign In
        </Link>
      </p>
    </form>
  );
};

export default ResetPasswordForm;
