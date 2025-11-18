interface StatCardProps {
    title: string;
    value: string | number;
    change?: string;
    changeType?: 'positive' | 'negative';
    icon: React.ComponentType<{ className?: string }>;
}

export const StatCard = ({ title, value, change, changeType = 'positive', icon: Icon }: StatCardProps) => {
    return (
        <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
            <div className="flex items-center justify-between">
                <div className="flex items-center">
                    <div className="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                        <Icon className="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                </div>
                {change && (
                    <span
                        className={`text-sm font-medium ${
                            changeType === 'positive'
                                ? 'text-green-600 dark:text-green-400'
                                : 'text-red-600 dark:text-red-400'
                        }`}
                    >
                        {change}
                    </span>
                )}
            </div>
            <div className="mt-4">
                <p className="text-sm font-medium text-gray-600 dark:text-gray-400">{title}</p>
                <p className="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{value}</p>
            </div>
        </div>
    );
};

interface BadgeProps {
    children: React.ReactNode;
    variant?: 'success' | 'warning' | 'danger' | 'info' | 'default';
}

export const Badge = ({ children, variant = 'default' }: BadgeProps) => {
    const variantClasses = {
        success: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        danger: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        info: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        default: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400',
    };

    return (
        <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${variantClasses[variant]}`}>
            {children}
        </span>
    );
};

interface EmptyStateProps {
    title: string;
    description?: string;
    icon?: React.ComponentType<{ className?: string }>;
    action?: {
        label: string;
        onClick: () => void;
    };
}

export const EmptyState = ({ title, description, icon: Icon, action }: EmptyStateProps) => {
    return (
        <div className="text-center py-12">
            {Icon && (
                <Icon className="mx-auto h-12 w-12 text-gray-400" />
            )}
            <h3 className="mt-2 text-sm font-medium text-gray-900 dark:text-white">{title}</h3>
            {description && (
                <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">{description}</p>
            )}
            {action && (
                <div className="mt-6">
                    <button
                        type="button"
                        onClick={action.onClick}
                        className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        {action.label}
                    </button>
                </div>
            )}
        </div>
    );
};
