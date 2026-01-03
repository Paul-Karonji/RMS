import { forwardRef } from 'react';

const Input = forwardRef(({
  label,
  type = 'text',
  error,
  hint,
  className = '',
  ...props
}, ref) => {
  return (
    <div className="w-full">
      {label && (
        <label className="block text-sm font-medium text-text mb-1.5">
          {label}
        </label>
      )}
      <input
        ref={ref}
        type={type}
        className={`
          w-full px-4 py-3 rounded-lg border transition-all duration-200
          text-text placeholder:text-muted
          focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary
          ${error 
            ? 'border-error focus:ring-error focus:border-error' 
            : 'border-slate-300 hover:border-slate-400'
          }
          ${className}
        `}
        {...props}
      />
      {error && (
        <p className="mt-1.5 text-sm text-error">{error}</p>
      )}
      {hint && !error && (
        <p className="mt-1.5 text-sm text-muted">{hint}</p>
      )}
    </div>
  );
});

Input.displayName = 'Input';

export default Input;
