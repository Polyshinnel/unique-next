import type { Metadata } from 'next';
import Link from 'next/link';
import { getPageSeo, toMetadata } from '@/lib/seo';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { siteContacts } from '@/lib/site-content';
import {
    Button,
    Container,
    Image,
    SimpleGrid,
    Stack,
    Text,
    TextInput,
    Textarea,
    Title,
} from '@mantine/core';
import { IconPhotoScan, IconReceiptTax, IconTruckDelivery } from '@tabler/icons-react';

export async function generateMetadata(): Promise<Metadata> {
    const seo = await getPageSeo('why_we');

    return toMetadata(seo, {
        title: 'Почему мы | ЮНИК С',
        description: 'Почему клиенты выбирают ЮНИК С при покупке б/у промышленного оборудования.',
    });
}

const questionItems = [
    'Кто поставщик, какова его репутация. Как давно он на рынке и насколько прозрачен.',
    'Каковы условия поставки, оплаты и сроки. Какие гарантии и чья ответственность.',
    'Главный вопрос - это в каком состоянии оборудование и как его проверить.',
    'Кем и за чей счет будут решаться вопросы демонтажа, погрузки, поиска транспорта и т.д.',
] as const;

const advantages = [
    {
        icon: IconReceiptTax,
        title: 'Надежного партнера',
        text: 'Мы платим НДС, по запросу предоставим бухгалтерскую отчетность и уставные документы. Берем на себя ответственность и соблюдаем договоренности.',
    },
    {
        icon: IconPhotoScan,
        title: 'Понятное состояние',
        text: 'Предоставим всю имеющуюся у нас информацию, а так же при необходимости сделаем доп. фото или покажем по видео-связи. Организуем проверку в работе.',
    },
    {
        icon: IconTruckDelivery,
        title: 'Минимум головной боли',
        text: 'Прикинем стоимость доставки или демонтажа. Найдем машину или подрядчика на ваших условиях. Проконтролируем и отчитаемся о ходе демонтажа и погрузки.',
    },
] as const;

const alreadyDoneList = [
    'Осмотрели и оценили оборудование',
    'Сделали общие фотографии, а также отдельные основных узлов и агрегатов',
    'Сняли видео в работе',
    'Обговорили все условия и сроки с компанией-поставщиком',
    'Заморозили цену и сделали любую удобную форму расчета: безнал с НДС или без НДС',
    'Согласовали процедуру вывода оборудования с баланса',
    'Оформили наши отношения с поставщиком ОФИЦИАЛЬНО',
    'Проработали сопутствующие вопросы, касающиеся демонтажа и погрузки',
    'Зачастую, уже выкупили ту или иную единицу оборудования',
] as const;

function HeroSection() {
    return (
        <section className="page-hero why-we-hero">
            <Container size="xl">
                <div className="catalog-breadcrumbs">
                    <Link href="/">Главная</Link>
                    <span>/</span>
                    <Link href="/about">О компании</Link>
                    <span>/</span>
                    <span>Почему мы</span>
                </div>
                <Stack gap="lg" maw={920}>
                    <Title order={1}>Почему мы</Title>
                    <Text size="lg">
                        “ООО «Юник С» – торгующая компания, мы занимаемся реализацией б.у. оборудования от
                        производственных предприятий на договорных условиях”
                    </Text>
                </Stack>
            </Container>
        </section>
    );
}

function SupplierChoiceSection() {
    return (
        <section className="content-section content-section--white">
            <Container size="xl">
                <div className="why-we-intro">
                    <div className="why-we-intro__content">
                        <Stack gap="lg">
                            <Title order={2}>Выбор поставщика промышленного оборудования</Title>
                            <Text size="lg">
                                Любая производственная компания в процессе своей работы рано или поздно сталкивается с
                                необходимостью выбора надежного партнера, будет ли это поставщик повседневной
                                концелярии или поставщик сложного промышленного оборудования.
                            </Text>
                        </Stack>
                    </div>

                    <div className="why-we-intro__media">
                        <Image
                            src="/assets/img/services/import-page/import-block.jpeg"
                            alt="Промышленное оборудование"
                        />
                    </div>
                </div>
            </Container>
        </section>
    );
}

function QuestionsSection() {
    return (
        <section className="content-section">
            <Container size="xl">
                <Stack gap="xl">
                    <Title order={2}>На какие вопросы следует ответить, выбирая поставщика б/у оборудования</Title>
                    <SimpleGrid cols={1} spacing="md">
                        {questionItems.map((item, index) => (
                            <div key={item} className="sales-list-item sales-list-item--surface">
                                <span className="sales-list-item__dot" />
                                <Text>
                                    <strong>{index + 1}.</strong> {item}
                                </Text>
                            </div>
                        ))}
                    </SimpleGrid>
                </Stack>
            </Container>
        </section>
    );
}

function SloganSection() {
    return (
        <section className="content-section sales-quote-section">
            <Container size="xl">
                <blockquote className="sales-quote-card">
                    <p>
                        ООО «Юник С» – торгующая компания, мы занимаемся реализацией б.у. оборудования от
                        производственных предприятий на договорных условиях
                    </p>
                </blockquote>
            </Container>
        </section>
    );
}

function AdvantagesSection() {
    return (
        <section className="content-section content-section--white">
            <Container size="xl">
                <Stack gap="xl">
                    <Title order={2}>Что вы получите, покупая оборудование у нас:</Title>

                    <SimpleGrid cols={{ base: 1, md: 3 }} spacing="lg">
                        {advantages.map((item) => {
                            const Icon = item.icon;

                            return (
                                <article key={item.title} className="sales-advantage-card">
                                    <span className="sales-advantage-card__icon">
                                        <Icon size={24} />
                                    </span>
                                    <Title order={3}>{item.title}</Title>
                                    <Text c="dimmed">{item.text}</Text>
                                </article>
                            );
                        })}
                    </SimpleGrid>
                </Stack>
            </Container>
        </section>
    );
}

function SummarySection() {
    return (
        <section className="content-section">
            <Container size="xl">
                <div className="sales-summary-card">
                    <Stack gap="xl">
                        <Title order={2}>Другими словами, мы уже:</Title>
                        <SimpleGrid cols={1} spacing="md">
                            {alreadyDoneList.map((item) => (
                                <div key={item} className="sales-list-item">
                                    <span className="sales-list-item__dot" />
                                    <Text>{item}</Text>
                                </div>
                            ))}
                        </SimpleGrid>
                    </Stack>
                </div>
            </Container>
        </section>
    );
}

function ContactFormSection() {
    return (
        <section className="content-section content-section--white">
            <Container size="xl">
                <section className="buyout-form-card why-we-form-card">
                    <Stack gap="md">
                        <Text className="contact-card__eyebrow">Обратная связь</Text>
                        <Title order={2}>Напишите нам</Title>
                        <Text c="dimmed">
                            Если вам нужно подобрать оборудование или обсудить условия покупки, оставьте сообщение и
                            мы свяжемся с вами.
                        </Text>
                    </Stack>

                    <Stack gap="sm" mt="xl">
                        <TextInput label="Имя" placeholder="Как к вам обращаться" />
                        <TextInput label="Email" placeholder="example@mail.ru" type="email" />
                        <TextInput label="Телефон" placeholder="+7 (___) ___-__-__" />
                        <Textarea
                            label="Сообщение"
                            placeholder="Напишите, какое оборудование вас интересует и какие вопросы нужно решить"
                            minRows={5}
                        />
                    </Stack>

                    <Button size="lg" mt="xl">
                        Отправить сообщение
                    </Button>
                    <Text size="sm" c="dimmed" className="buyout-form-card__hint">
                        Также можно написать на {siteContacts.email} или позвонить по номеру {siteContacts.phone}.
                    </Text>
                </section>
            </Container>
        </section>
    );
}

export default function WhyWePage() {
    return (
        <>
            <Header />
            <main>
                <HeroSection />
                <SupplierChoiceSection />
                <QuestionsSection />
                <SloganSection />
                <AdvantagesSection />
                <SummarySection />
                <ContactFormSection />
            </main>
            <Footer />
        </>
    );
}
