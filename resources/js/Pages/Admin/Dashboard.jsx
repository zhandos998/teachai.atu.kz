import AdminLayout from "@/Layouts/AdminLayout";

export default function Dashboard() {
    return (
        <AdminLayout>
            {/* Заголовок */}
            <div>
                <h1 className="text-3xl font-bold text-[#21397D] mb-2">
                    Добро пожаловать 👋
                </h1>
                <p className="text-gray-600">
                    Управляйте документами в удобной админ-панели.
                </p>
            </div>

            {/* Основной контент */}
            <div className="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                {/* Карточка 1 */}
                <div className="bg-white shadow rounded-lg p-6 border border-gray-100 hover:shadow-lg transition">
                    <h2 className="text-lg font-semibold text-[#21397D] mb-2">
                        Документы
                    </h2>
                    <p className="text-gray-600 mb-4">
                        Добавляйте, редактируйте и удаляйте разделы.
                    </p>
                    <a
                        href="/admin/documents"
                        className="inline-block px-4 py-2 bg-[#21397D] text-white rounded hover:bg-[#1e2d63] transition"
                    >
                        Перейти →
                    </a>
                </div>

                {/* Карточка 2 */}
                <div className="bg-white shadow rounded-lg p-6 border border-gray-100 hover:shadow-lg transition">
                    <h2 className="text-lg font-semibold text-[#21397D] mb-2">
                        AI Логи
                    </h2>
                    <p className="text-gray-600 mb-4">
                        Следите за тем, как AI классифицирует вопросы.
                    </p>
                    <a
                        href="/admin/ai-logs"
                        className="inline-block px-4 py-2 bg-[#21397D] text-white rounded hover:opacity-90"
                    >
                        Перейти →
                    </a>
                </div>

                {/* Карточка 3 */}
                {/* <div className="bg-white shadow rounded-lg p-6 border border-gray-100 hover:shadow-lg transition">
                    <h2 className="text-lg font-semibold text-[#14224C] mb-2">
                        Настройки системы
                    </h2>
                    <p className="text-gray-600 mb-4">
                        Редактируйте ключи, токены и общие параметры.
                    </p>
                    <a
                        href="#"
                        className="inline-block px-4 py-2 bg-[#14224C] text-white rounded opacity-50 cursor-not-allowed"
                    >
                        В разработке →
                    </a>
                </div> */}
            </div>
        </AdminLayout>
    );
}
