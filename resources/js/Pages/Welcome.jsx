import React from "react";
import AppLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link } from "@inertiajs/react";

export default function Welcome({ auth }) {
    return (
        <AppLayout>
            <Head title="Добро пожаловать" />

            <div className="bg-white text-gray-900 shadow rounded-lg p-4 mb-10">
                <h1 className="text-xl font-semibold mb-3">
                    Добро пожаловать!
                </h1>

                <p className="leading-relaxed mb-6 text-gray-700">
                    Для использования системы необходимо выполнить вход или
                    пройти регистрацию. После авторизации станут доступны все
                    функции личного кабинета — заполнение индивидуального плана,
                    загрузка подтверждающих файлов, просмотр статусов и итоговых
                    показателей.
                </p>

                {!auth.user && (
                    <div className="flex gap-4">
                        <Link
                            href={route("login")}
                            className="text-white px-4 py-2 rounded transition"
                            style={{ backgroundColor: "#21397D" }}
                        >
                            Войти
                        </Link>

                        <Link
                            href={route("register")}
                            className=" text-white px-4 py-2 rounded transition"
                            style={{ backgroundColor: "#21397D" }}
                        >
                            Регистрация
                        </Link>
                    </div>
                )}

                {auth.user && (
                    <div className="mt-4">
                        <Link
                            href={route("dashboard")}
                            className=" text-white px-4 py-2 rounded transition"
                            style={{ backgroundColor: "#21397D" }}
                        >
                            Перейти в панель
                        </Link>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
