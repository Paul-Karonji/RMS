const Card = ({ children, className = '', padding = 'md' }) => {
  const paddingSizes = {
    sm: 'p-4',
    md: 'p-6',
    lg: 'p-8',
  };

  return (
    <div
      className={`
        bg-surface rounded-lg shadow-md border border-slate-100
        ${paddingSizes[padding]}
        ${className}
      `}
    >
      {children}
    </div>
  );
};

export default Card;
