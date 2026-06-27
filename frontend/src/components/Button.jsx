import React from 'react';

export default function Button({ children, onClick, type = 'button', className = '', variant = 'primary' }) {
  const baseClasses = 'inline-flex justify-center py-2 px-4 border shadow-sm text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2';
  
  const variants = {
    primary: 'border-transparent text-white bg-primary hover:bg-primary-hover focus:ring-primary',
    secondary: 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-primary',
    danger: 'border-transparent text-white bg-red-600 hover:bg-red-700 focus:ring-red-500',
  };

  return (
    <button
      type={type}
      onClick={onClick}
      className={`${baseClasses} ${variants[variant]} ${className}`}
    >
      {children}
    </button>
  );
}
